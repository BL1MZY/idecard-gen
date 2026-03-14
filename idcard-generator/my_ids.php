<?php
require __DIR__ . '/auth_check.php';
require __DIR__ . '/db.php';

$userId   = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8');

$stmt = $pdo->prepare('
    SELECT id, full_name, role, department, employee_id, expiry_date, front_image, back_image, created_at
    FROM id_cards
    WHERE user_id = :user_id
    ORDER BY created_at DESC
');
$stmt->execute(['user_id' => $userId]);
$cards = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My ID Cards - De Switch Tech</title>
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

<body class="bg-slate-100 font-body">
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
<div class="mx-auto max-w-5xl px-4 py-8">
    
    <h1 class="text-2xl font-semibold mb-4">My Generated ID Cards</h1>
    <p class="text-sm text-slate-600 mb-6">Signed in as <?php echo $userName; ?></p>
    

    <?php if (!$cards): ?>
        <p class="text-sm text-slate-500">You have not generated any ID cards yet.</p>
    <?php else: ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach ($cards as $card): ?>
                <div class="rounded-xl bg-white border border-slate-200 shadow-sm p-4 text-sm">
                    <p class="font-semibold text-slate-900">
                        <?php echo htmlspecialchars($card['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-slate-600">
                        <?php echo htmlspecialchars($card['role'], ENT_QUOTES, 'UTF-8'); ?>
                        • <?php echo htmlspecialchars($card['department'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-1">
                        ID: <?php echo htmlspecialchars($card['employee_id'], ENT_QUOTES, 'UTF-8'); ?>
                        • Expires: <?php echo htmlspecialchars($card['expiry_date'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="mt-3 flex gap-2">
                    <a href="..." class="inline-flex items-center justify-center rounded-lg bg-crimson-600 px-3 py-1 text-xs font-semibold text-white hover:bg-crimson-700">
    View Front
</a>

                        <a href="<?php echo htmlspecialchars($card['back_image'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank"
                           class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">
                            View Back
                        </a>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400">
                        Created: <?php echo htmlspecialchars($card['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
