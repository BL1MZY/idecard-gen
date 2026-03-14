<?php
// register.php
session_start();

require __DIR__ . '/db.php';

$error  = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName       = trim($_POST['full_name'] ?? '');
    $username       = trim($_POST['username'] ?? '');
    $password       = trim($_POST['password'] ?? '');
    $passwordRepeat = trim($_POST['password_confirm'] ?? '');

    if ($fullName === '' || $username === '' || $password === '' || $passwordRepeat === '') {
        $error = 'All fields are required.';
    } elseif ($password !== $passwordRepeat) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $error = 'That username is already taken. Please choose another.';
        } else {
            // Create account
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('
                INSERT INTO users (username, full_name, password_hash)
                VALUES (:username, :full_name, :password_hash)
            ');

            try {
                $stmt->execute([
                    'username'      => $username,
                    'full_name'     => $fullName,
                    'password_hash' => $passwordHash,
                ]);

                // Option A: auto-login
                // $_SESSION['user_id']   = $pdo->lastInsertId();
                // $_SESSION['user_name'] = $fullName;
                // header('Location: index.php');
                // exit;

                // Option B: send them to login page
                $success = 'Account created successfully. You can now sign in.';
            } catch (PDOException $e) {
                $error = 'An error occurred while creating your account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Create Account - De Switch Tech Portal</title>

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
<body class="h-full font-body bg-slate-100">
<div class="min-h-screen flex">

    <!-- Left panel can be identical to login.php for consistent branding -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-7/12 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-crimson-900 via-crimson-700 to-slate-900"></div>
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,_#ffffff33,_transparent_55%)]"></div>

        <div class="relative z-10 flex flex-col justify-between w-full px-12 py-10 xl:px-16 xl:py-14">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-full border border-crimson-200/60 bg-white/10 flex items-center justify-center">
                    <span class="text-xl font-semibold text-crimson-100 font-display tracking-wide">DS</span>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-crimson-100/80 font-semibold">
                        De Switch Tech
                    </p>
                    <p class="text-xl sm:text-2xl font-display text-white tracking-tight">
                        School Portal
                    </p>
                </div>
            </div>

            <div class="mt-12 lg:mt-0 max-w-xl">
                <p class="text-xs uppercase tracking-[0.3em] text-crimson-100/70 mb-3">
                    Student Information System
                </p>
                <h2 class="text-3xl md:text-4xl xl:text-5xl font-display text-white leading-tight">
                    Create your<br class="hidden md:block"> student portal account.
                </h2>
                <p class="mt-4 text-sm md:text-base text-crimson-50/90 leading-relaxed max-w-lg">
                    Register once to access your digital ID, academic history, and
                    personalized student services.
                </p>
            </div>

            <div class="mt-10 flex items-center justify-between text-xs text-crimson-100/70">
                <p>© <?php echo date('Y'); ?> De Switch Tech. All rights reserved.</p>
                <p class="hidden sm:block">Office of Information Technology Services</p>
            </div>
        </div>
    </div>

    <!-- Right panel: registration form -->
    <div class="flex-1 flex items-center justify-center px-6 py-10 sm:px-8 lg:px-10">
        <div class="w-full max-w-md">
            <div class="mb-8 flex flex-col items-center text-center">
                <div class="inline-flex items-center rounded-full border border-crimson-100 bg-crimson-50 px-3 py-1 text-xs font-medium text-crimson-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-crimson-500 mr-2"></span>
                    Create New Student Account
                </div>

                <h1 class="mt-5 text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">
                    De Switch Tech Portal
                </h1>
                <p class="mt-2 text-sm text-slate-600 max-w-sm">
                    Fill in your details to create a secure account for the
                    <span class="font-medium text-slate-800">ID Card Generator</span> and other portal features.
                </p>
            </div>

            <!-- Error / success messages -->
            <?php if ($error): ?>
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-start gap-3">
                    <div class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 6a1 1 0 012 0v4a1 1 0 11-2 0V6zm1 8a1.25 1.25 0 110-2.5A1.25 1.25 0 0110 14z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3">
                    <div class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 10-1.414 1.414L9 13.414l4.707-4.707z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Registration card -->
            <div class="rounded-2xl bg-white shadow-md shadow-slate-200/70 border border-slate-100 px-6 py-6 sm:px-7 sm:py-7">
                <form action="register.php" method="POST" class="space-y-5">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-slate-800">
                            Full Name
                        </label>
                        <div class="mt-1.5">
                            <input
                                type="text"
                                name="full_name"
                                id="full_name"
                                required
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                placeholder="e.g. Demo Student"
                                value="<?php echo htmlspecialchars($fullName ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-800">
                            Username
                        </label>
                        <div class="mt-1.5">
                            <input
                                type="text"
                                name="username"
                                id="username"
                                required
                                autocomplete="username"
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                placeholder="e.g. j.doe23"
                                value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-800">
                            Password
                        </label>
                        <div class="mt-1.5">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                placeholder="Create a strong password"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-slate-800">
                            Confirm Password
                        </label>
                        <div class="mt-1.5">
                            <input
                                type="password"
                                name="password_confirm"
                                id="password_confirm"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                placeholder="Repeat your password"
                            >
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-lg bg-crimson-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white transition"
                    >
                        Create account
                    </button>
                </form>

                <p class="mt-5 text-[11px] leading-relaxed text-slate-500 text-center">
                    Already have an account?
                    <a href="login.php" class="font-medium text-crimson-600 hover:text-crimson-700">
                        Return to login
                    </a>
                </p>
            </div>

            <p class="mt-5 text-[11px] text-center text-slate-500 leading-relaxed">
                Use of this system is governed by institutional policies. Unauthorized access
                is prohibited and may result in disciplinary action.
            </p>
        </div>
    </div>
</div>
</body>
</html>
