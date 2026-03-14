<?php
require __DIR__ . '/auth_check.php';
require __DIR__ . '/db.php';
// generate.php

// Simple helper to abort with a Harvard-styled error page
function abort_with_message(string $msg): void {
    $safeMsg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <title>Error - De Switch Tech Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            crimson: {
              50:  '#fdf3f5',
              100: '#f9dde1',
              200: '#f1b5c0',
              300: '#e57b8f',
              400: '#d94464',
              500: '#c91f44',
              600: '#a51c30',
              700: '#821222',
              800: '#5d0d19',
              900: '#3c070f',
            }
          },
          fontFamily: {
            display: ['"Times New Roman"', 'ui-serif', 'Georgia', 'serif'],
            body: ['system-ui', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
          }
        }
      }
    }
  </script>
</head>
<body class="h-full bg-slate-100 font-body">
<div class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/70 px-6 py-6">
    <div class="mb-4 inline-flex items-center rounded-full border border-red-100 bg-red-50 px-3 py-1 text-[11px] font-medium text-red-700">
      <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-2"></span>
      Request Error
    </div>
    <h1 class="text-xl font-semibold text-slate-900 mb-2">Unable to Generate ID Card</h1>
    <p class="text-sm text-slate-600 mb-4">{$safeMsg}</p>
    <p class="text-[11px] text-slate-500 mb-6">
      Please review your input and try again. If the issue persists, contact your ICT support team.
    </p>
    <div class="flex flex-wrap gap-2">
      <a href="index.php" class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg bg-crimson-600 text-white hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2">
        ← Back to ID Card Form
      </a>
      <a href="login.php" class="inline-flex justify-center items-center px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 text-slate-700 bg-slate-50 hover:bg-white">
        Return to Login
      </a>
    </div>
  </div>
</div>
</body>
</html>
HTML;
    exit;
}

// 1. Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort_with_message("Invalid request method.");
}

// 2. Collect & validate input fields
$fullName   = isset($_POST['full_name'])   ? trim($_POST['full_name'])   : '';
$role       = isset($_POST['role'])        ? trim($_POST['role'])        : '';
$department = isset($_POST['department'])  ? trim($_POST['department'])  : '';
$employeeId = isset($_POST['employee_id']) ? trim($_POST['employee_id']) : '';
$expiryDate = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';

if ($fullName === '' || $role === '' || $department === '' || $employeeId === '' || $expiryDate === '') {
    abort_with_message("All fields are required.");
}

// 3. Validate and handle uploaded photo
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    abort_with_message("Photo upload failed. Please try again.");
}

$photoTmpPath = $_FILES['photo']['tmp_name'];
$photoSize    = $_FILES['photo']['size'];

if ($photoSize <= 0) {
    abort_with_message("Uploaded photo seems to be empty.");
}

// Check image type
$imgInfo = getimagesize($photoTmpPath);
if ($imgInfo === false) {
    abort_with_message("Uploaded file is not a valid image.");
}
$mime = $imgInfo['mime'];

switch ($mime) {
    case 'image/jpeg':
    case 'image/jpg':
        $photoResource = imagecreatefromjpeg($photoTmpPath);
        break;
    case 'image/png':
        $photoResource = imagecreatefrompng($photoTmpPath);
        break;
    default:
        abort_with_message("Only JPG and PNG images are allowed.");
}

if (!$photoResource) {
    abort_with_message("Unable to read the uploaded image.");
}

// 4. Create card images (front & back)
$cardWidth  = 1000;
$cardHeight = 600;

// 5. Setup fonts (using Windows system fonts)
$fontBoldPath    = 'C:/Windows/Fonts/arialbd.ttf';  // Arial Bold
$fontRegularPath = 'C:/Windows/Fonts/arial.ttf';    // Arial Regular

if (!file_exists($fontBoldPath) || !file_exists($fontRegularPath)) {
    abort_with_message("System font files not found. Adjust font paths in generate.php.");
}

/**
 * Helper to allocate Harvard-style colors on a given image
 */
function allocate_harvard_colors($img): array {
    $white      = imagecolorallocate($img, 255, 255, 255);
    $offWhite   = imagecolorallocate($img, 249, 245, 241); // parchment-like
    $lightGray  = imagecolorallocate($img, 240, 240, 240);
    $borderGray = imagecolorallocate($img, 210, 210, 210);
    $black      = imagecolorallocate($img, 20, 20, 20);
    $darkCrimson   = imagecolorallocate($img, 165, 28, 48);  // #A51C30
    $accentCrimson = imagecolorallocate($img, 201, 31, 68);  // #C91F44
    $gray       = imagecolorallocate($img, 80, 80, 80);

    return compact('white','offWhite','lightGray','borderGray','black','darkCrimson','accentCrimson','gray');
}

/* ======================== FRONT SIDE ======================== */

$cardFront = imagecreatetruecolor($cardWidth, $cardHeight);
if (!$cardFront) {
    abort_with_message("Failed to create ID card image (front).");
}

extract(allocate_harvard_colors($cardFront));

// Fill background with off-white
imagefilledrectangle($cardFront, 0, 0, $cardWidth - 1, $cardHeight - 1, $offWhite);

// Draw outer border
imagerectangle($cardFront, 10, 10, $cardWidth - 11, $cardHeight - 11, $borderGray);

// Header bar
$headerHeight = 90;
imagefilledrectangle($cardFront, 10, 10, $cardWidth - 11, 10 + $headerHeight, $darkCrimson);

// Footer bar
$footerHeight = 50;
imagefilledrectangle($cardFront, 10, $cardHeight - 10 - $footerHeight, $cardWidth - 11, $cardHeight - 11, $lightGray);

// Place brand title in header
$brandText = "De Switch Tech";
imagettftext($cardFront, 30, 0, 40, 10 + 60, $white, $fontBoldPath, $brandText);

// Optional tagline
$tagline = "Official Identification Card";
imagettftext($cardFront, 16, 0, 40, 10 + 85, $white, $fontRegularPath, $tagline);

// Photo box (left side)
$photoBoxX      = 60;
$photoBoxY      = 140;
$photoBoxWidth  = 250;
$photoBoxHeight = 320;

// Background for photo area
imagefilledrectangle(
    $cardFront,
    $photoBoxX,
    $photoBoxY,
    $photoBoxX + $photoBoxWidth,
    $photoBoxY + $photoBoxHeight,
    $lightGray
);

// Border around photo area
imagerectangle(
    $cardFront,
    $photoBoxX,
    $photoBoxY,
    $photoBoxX + $photoBoxWidth,
    $photoBoxY + $photoBoxHeight,
    $accentCrimson
);

$srcWidth  = imagesx($photoResource);
$srcHeight = imagesy($photoResource);

// Keep aspect ratio while fitting into the box
$srcRatio = $srcWidth / $srcHeight;
$dstRatio = $photoBoxWidth / $photoBoxHeight;

if ($srcRatio > $dstRatio) {
    // Source is wider: limit by width
    $newWidth  = $photoBoxWidth;
    $newHeight = (int) round($photoBoxWidth / $srcRatio);
} else {
    // Source is taller: limit by height
    $newHeight = $photoBoxHeight;
    $newWidth  = (int) round($photoBoxHeight * $srcRatio);
}

// Center inside box
$dstX = $photoBoxX + (int) round(($photoBoxWidth - $newWidth) / 2);
$dstY = $photoBoxY + (int) round(($photoBoxHeight - $newHeight) / 2);

imagecopyresampled(
    $cardFront,
    $photoResource,
    $dstX, $dstY,
    0, 0,
    $newWidth, $newHeight,
    $srcWidth, $srcHeight
);

// Right-side text fields
$textBaseX = 360;
$nameY     = 180;
$roleY     = 230;
$deptY     = 280;
$idY       = 330;
$expY      = 380;

// Full name
imagettftext($cardFront, 26, 0, $textBaseX, $nameY, $black, $fontBoldPath, $fullName);

// Role / position
imagettftext($cardFront, 18, 0, $textBaseX, $roleY, $accentCrimson, $fontRegularPath, "Role: " . $role);

// Department
imagettftext($cardFront, 18, 0, $textBaseX, $deptY, $gray, $fontRegularPath, "Department: " . $department);

// Employee ID
imagettftext($cardFront, 18, 0, $textBaseX, $idY, $black, $fontRegularPath, "ID: " . $employeeId);

// Expiry date
imagettftext($cardFront, 18, 0, $textBaseX, $expY, $black, $fontRegularPath, "Expiry: " . $expiryDate);

// Footer text
$footerText = "Property of De Switch Tech • Unauthorized use is prohibited.";
imagettftext(
    $cardFront,
    12,
    0,
    40,
    $cardHeight - 26,
    $gray,
    $fontRegularPath,
    $footerText
);

// We no longer need the source photo resource
imagedestroy($photoResource);

/* ======================== BACK SIDE ======================== */

$cardBack = imagecreatetruecolor($cardWidth, $cardHeight);
if (!$cardBack) {
    abort_with_message("Failed to create ID card image (back).");
}

extract(allocate_harvard_colors($cardBack));

// Background + border
imagefilledrectangle($cardBack, 0, 0, $cardWidth - 1, $cardHeight - 1, $offWhite);
imagerectangle($cardBack, 10, 10, $cardWidth - 11, $cardHeight - 11, $borderGray);

// Header strip
$backHeaderHeight = 70;
imagefilledrectangle($cardBack, 10, 10, $cardWidth - 11, 10 + $backHeaderHeight, $darkCrimson);

$backTitle = "Cardholder Information";
imagettftext($cardBack, 22, 0, 40, 10 + 45, $white, $fontBoldPath, $backTitle);

// Left column: details
$leftX   = 60;
$baseY   = 130;
$lineGap = 40;

imagettftext($cardBack, 16, 0, $leftX, $baseY, $gray, $fontRegularPath, "Name:");
imagettftext($cardBack, 20, 0, $leftX + 140, $baseY, $black, $fontBoldPath, $fullName);

imagettftext($cardBack, 16, 0, $leftX, $baseY + $lineGap, $gray, $fontRegularPath, "Role:");
imagettftext($cardBack, 18, 0, $leftX + 140, $baseY + $lineGap, $black, $fontRegularPath, $role);

imagettftext($cardBack, 16, 0, $leftX, $baseY + 2*$lineGap, $gray, $fontRegularPath, "Department:");
imagettftext($cardBack, 18, 0, $leftX + 140, $baseY + 2*$lineGap, $black, $fontRegularPath, $department);

imagettftext($cardBack, 16, 0, $leftX, $baseY + 3*$lineGap, $gray, $fontRegularPath, "ID Number:");
imagettftext($cardBack, 18, 0, $leftX + 140, $baseY + 3*$lineGap, $black, $fontRegularPath, $employeeId);

imagettftext($cardBack, 16, 0, $leftX, $baseY + 4*$lineGap, $gray, $fontRegularPath, "Valid Until:");
imagettftext($cardBack, 18, 0, $leftX + 140, $baseY + 4*$lineGap, $black, $fontRegularPath, $expiryDate);

// Right column: instructions box
$instrBoxX1 = 560;
$instrBoxY1 = 140;
$instrBoxX2 = $cardWidth - 60;
$instrBoxY2 = 400;

imagefilledrectangle($cardBack, $instrBoxX1, $instrBoxY1, $instrBoxX2, $instrBoxY2, $white);
imagerectangle($cardBack, $instrBoxX1, $instrBoxY1, $instrBoxX2, $instrBoxY2, $borderGray);

imagettftext($cardBack, 16, 0, $instrBoxX1 + 20, $instrBoxY1 + 35, $darkCrimson, $fontBoldPath, "Usage & Return");

$instrText1 = "This identification card is issued by De Switch Tech";
$instrText2 = "for official academic and administrative purposes.";
$instrText3 = "If found, please return to the institution's office";
$instrText4 = "or contact the ICT/Records department.";

imagettftext($cardBack, 13, 0, $instrBoxX1 + 20, $instrBoxY1 + 70, $gray, $fontRegularPath, $instrText1);
imagettftext($cardBack, 13, 0, $instrBoxX1 + 20, $instrBoxY1 + 90, $gray, $fontRegularPath, $instrText2);
imagettftext($cardBack, 13, 0, $instrBoxX1 + 20, $instrBoxY1 + 120, $gray, $fontRegularPath, $instrText3);
imagettftext($cardBack, 13, 0, $instrBoxX1 + 20, $instrBoxY1 + 140, $gray, $fontRegularPath, $instrText4);

// Simulated barcode/QR area
$barcodeX1 = $cardWidth - 260;
$barcodeY1 = $cardHeight - 180;
$barcodeX2 = $cardWidth - 60;
$barcodeY2 = $cardHeight - 80;

imagefilledrectangle($cardBack, $barcodeX1, $barcodeY1, $barcodeX2, $barcodeY2, $lightGray);
imagerectangle($cardBack, $barcodeX1, $barcodeY1, $barcodeX2, $barcodeY2, $borderGray);
imagettftext($cardBack, 12, 0, $barcodeX1 + 30, $barcodeY1 + 55, $gray, $fontRegularPath, "Barcode / QR Code");

// Back footer
$backFooterText = "By using this card, you agree to abide by De Switch Tech policies.";
imagettftext(
    $cardBack,
    12,
    0,
    40,
    $cardHeight - 26,
    $gray,
    $fontRegularPath,
    $backFooterText
);

// 11. Save generated cards to /cards directory
$cardsDir = __DIR__ . '/cards';
if (!is_dir($cardsDir)) {
    mkdir($cardsDir, 0777, true);
}

$timestamp = time();
$rand      = rand(1000, 9999);

$frontFilename = 'idcard_front_' . $timestamp . '_' . $rand . '.png';
$backFilename  = 'idcard_back_'  . $timestamp . '_' . $rand . '.png';

$frontPath = $cardsDir . '/' . $frontFilename;
$backPath  = $cardsDir . '/' . $backFilename;

if (!imagepng($cardFront, $frontPath)) {
  abort_with_message("Failed to save the generated front ID card image.");
}
if (!imagepng($cardBack, $backPath)) {
  abort_with_message("Failed to save the generated back ID card image.");
}

// Clean up image resources
imagedestroy($cardFront);
imagedestroy($cardBack);

// Build relative paths to store in DB and show in HTML
$frontRelativePath = 'cards/' . $frontFilename;
$backRelativePath  = 'cards/' . $backFilename;

// 12. Store ID record in database
$userId = $_SESSION['user_id'] ?? null;

if ($userId === null) {
  abort_with_message("You are not logged in. Cannot store ID record.");
}

try {
  $stmt = $pdo->prepare('
      INSERT INTO id_cards (
          user_id, full_name, role, department, employee_id, expiry_date,
          front_image, back_image
      ) VALUES (
          :user_id, :full_name, :role, :department, :employee_id, :expiry_date,
          :front_image, :back_image
      )
  ');

  $stmt->execute([
      'user_id'     => $userId,
      'full_name'   => $fullName,
      'role'        => $role,
      'department'  => $department,
      'employee_id' => $employeeId,
      'expiry_date' => $expiryDate,           // 'YYYY-MM-DD' from the form
      'front_image' => $frontRelativePath,    // store relative paths
      'back_image'  => $backRelativePath,
  ]);

  $cardId = $pdo->lastInsertId(); // if you want to reference this later
} catch (PDOException $e) {
  abort_with_message("Unable to store ID record in the database.");
}

// 13. Show HTML with the generated front & back cards
$displayName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
$userName    = htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <title>ID Card Generated - De Switch Tech</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
      tailwind.config = {
          theme: {
              extend: {
                  colors: {
                      crimson: {
                          50:  '#fdf3f5',
                          100: '#f9dde1',
                          200: '#f1b5c0',
                          300: '#e57b8f',
                          400: '#d94464',
                          500: '#c91f44',
                          600: '#a51c30',
                          700: '#821222',
                          800: '#5d0d19',
                          900: '#3c070f',
                      }
                  },
                  fontFamily: {
                      display: ['"Times New Roman"', 'ui-serif', 'Georgia', 'serif'],
                      body: ['system-ui', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
                  }
              }
          }
      }
  </script>
</head>
<body class="h-full bg-slate-100 font-body">
<div class="min-h-screen flex flex-col">

  <!-- Top Navigation Bar -->
  <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Left: Logo + Name -->
            <div class="flex items-center gap-3">
                <img
                    src="assets/deswitch-tech-hero.png"
                    alt="De Switch Tech Logo"
                    class="h-40 w-auto object-contain"
                >
                <div class="leading-tight">
                    <p class="text-[10px] uppercase tracking-[0.25em] text-crimson-700 font-semibold">
                        De Switch Tech
                    </p>
                    <p class="text-sm sm:text-base font-semibold text-slate-900">
                        School Portal
                    </p>
                </div>
            </div>

            <!-- Right: User info + Actions -->
            <div class="flex items-center gap-3 sm:gap-4 text-xs sm:text-sm">
                <!-- My IDs link -->
                <a
                    href="my_ids.php"
                    class="hidden sm:inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-crimson-300 hover:text-crimson-700 transition"
                >
                    My IDs
                </a>

                <div class="text-right leading-tight">
                    <p class="text-slate-500">Signed in as</p>
                    <p class="font-medium text-slate-900">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>

                <a
                    href="logout.php"
                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-crimson-300 hover:text-crimson-700 transition"
                >
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

  <!-- Main Content -->
  <main class="flex-1">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 lg:py-10">

      <!-- Page Heading -->
      <div class="mb-6 sm:mb-8">
        <nav class="text-xs text-slate-500 mb-2">
          <ol class="flex items-center gap-1">
            <li>Dashboard</li>
            <li class="text-slate-400">/</li>
            <li>ID Card Generator</li>
            <li class="text-slate-400">/</li>
            <li class="font-medium text-slate-700">Result</li>
          </ol>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
          <div>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">
              ID Card Generated
            </h1>
            <p class="mt-2 text-sm text-slate-600 max-w-xl">
              The ID card for <span class="font-semibold text-slate-900"><?php echo $displayName; ?></span>
              has been successfully generated. Preview the <span class="font-semibold">front</span> and
              <span class="font-semibold">back</span> views below.
            </p>
          </div>
          <div class="inline-flex items-center rounded-full border border-crimson-100 bg-crimson-50 px-3 py-1 text-[11px] font-medium text-crimson-700">
            <span class="h-1.5 w-1.5 rounded-full bg-crimson-500 mr-2"></span>
            Official Identification • Dual-Sided
          </div>
        </div>
      </div>

      <!-- Front & Back Cards -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Front -->
        <section class="rounded-2xl bg-white border border-slate-100 shadow-sm shadow-slate-200/60 px-4 py-5 sm:px-5 sm:py-6">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h2 class="text-sm font-semibold text-slate-900">Front View</h2>
              <p class="text-xs text-slate-500">Primary ID display with photo and core details.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-crimson-50 px-2 py-0.5 text-[10px] font-medium text-crimson-700 border border-crimson-100">
              Front
            </span>
          </div>
          <div class="border border-slate-200 rounded-xl bg-slate-50 p-2 overflow-hidden">
            <div class="bg-slate-100 rounded-lg overflow-hidden">
              <img
                src="<?php echo htmlspecialchars($frontRelativePath, ENT_QUOTES, 'UTF-8'); ?>"
                alt="Generated ID Card Front"
                class="block w-full h-auto"
              >
            </div>
          </div>
          <div class="mt-4 flex flex-wrap gap-2">
            <a
              class="inline-flex items-center justify-center rounded-lg bg-crimson-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2"
              href="<?php echo htmlspecialchars($frontRelativePath, ENT_QUOTES, 'UTF-8'); ?>"
              download
            >
              Download Front (PNG)
            </a>
          </div>
        </section>

        <!-- Back -->
        <section class="rounded-2xl bg-white border border-slate-100 shadow-sm shadow-slate-200/60 px-4 py-5 sm:px-5 sm:py-6">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h2 class="text-sm font-semibold text-slate-900">Back View</h2>
              <p class="text-xs text-slate-500">Cardholder information, usage instructions, and return details.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-700 border border-slate-200">
              Back
            </span>
          </div>
          <div class="border border-slate-200 rounded-xl bg-slate-50 p-2 overflow-hidden">
            <div class="bg-slate-100 rounded-lg overflow-hidden">
              <img
                src="<?php echo htmlspecialchars($backRelativePath, ENT_QUOTES, 'UTF-8'); ?>"
                alt="Generated ID Card Back"
                class="block w-full h-auto"
              >
            </div>
          </div>
          <div class="mt-4 flex flex-wrap gap-2">
            <a
              class="inline-flex items-center justify-center rounded-lg bg-crimson-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2"
              href="<?php echo htmlspecialchars($backRelativePath, ENT_QUOTES, 'UTF-8'); ?>"
              download
            >
              Download Back (PNG)
            </a>
          </div>
        </section>
      </div>

      <!-- Global Actions -->
      <div class="mt-8 flex flex-wrap gap-3">
        <a
          href="index.php"
          class="inline-flex items-center justify-center rounded-lg bg-crimson-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2"
        >
          Generate Another ID
        </a>
        <a
          href="index.php"
          class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
          ← Back to ID Card Form
        </a>
      </div>

      <p class="mt-4 text-[11px] text-slate-500">
        You may print these images directly or place them into an institutional badge template for lamination.
        For best results, use high-quality cardstock and color printing.
      </p>
    </div>
  </main>

  <!-- Footer -->
  <footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-3">
      <p class="text-[11px] text-slate-500 text-center sm:text-left">
        © <?php echo date('Y'); ?> De Switch Tech. All rights reserved. • Office of Information & Digital Services
      </p>
    </div>
  </footer>
</div>
</body>
</html>
