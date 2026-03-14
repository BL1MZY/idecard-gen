<?php
// login.php
session_start();

require __DIR__ . '/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Look up user by username
        $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];

            header('Location: index.php'); // go to ID card form
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Login - De Switch Tech Portal</title>

    <!-- Tailwind via CDN -->
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
                            600: '#a51c30', // Harvard-style crimson
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

    <!-- Left panel: prestige / branding -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-7/12 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-crimson-900 via-crimson-700 to-slate-900"></div>

        <!-- Subtle overlay pattern -->
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,_#ffffff33,_transparent_55%)]"></div>

        <div class="relative z-10 flex flex-col justify-between w-full px-12 py-10 xl:px-16 xl:py-14">
            <!-- Top: crest + school name -->
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-full border border-crimson-200/60 bg-white/10 flex items-center justify-center">
                    <!-- You can replace this with your actual logo image -->
                    <!-- <img src="your-logo.png" alt="School Logo" class="h-10 w-10 object-contain"> -->
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

            <!-- Middle: quote / academic vibe -->
            <div class="mt-12 lg:mt-0 max-w-xl">
                <p class="text-xs uppercase tracking-[0.3em] text-crimson-100/70 mb-3">
                    Student Information System
                </p>
                <h2 class="text-3xl md:text-4xl xl:text-5xl font-display text-white leading-tight">
                    “Veritas through<br class="hidden md:block"> Technology & Learning.”
                </h2>
                <p class="mt-4 text-sm md:text-base text-crimson-50/90 leading-relaxed max-w-lg">
                    Access your digital ID card, academic records, and student services
                    in a secure, university-grade environment designed for excellence.
                </p>
            </div>

            <!-- Bottom: small footer -->
            <div class="mt-10 flex items-center justify-between text-xs text-crimson-100/70">
                <p>© <?php echo date('Y'); ?> De Switch Tech. All rights reserved.</p>
                <p class="hidden sm:block">Office of Information Technology Services</p>
            </div>
        </div>
    </div>

    <!-- Right panel: actual login form -->
    <div class="flex-1 flex items-center justify-center px-6 py-10 sm:px-8 lg:px-10">
        <div class="w-full max-w-md">
            <!-- Top badge -->
            <div class="mb-8 flex flex-col items-center text-center">
                <div class="inline-flex items-center rounded-full border border-crimson-100 bg-crimson-50 px-3 py-1 text-xs font-medium text-crimson-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-crimson-500 mr-2"></span>
                    Secure Student Login
                </div>

                <h1 class="mt-5 text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">
                    De Switch Tech Portal
                </h1>
                <p class="mt-2 text-sm text-slate-600 max-w-sm">
                    Sign in with your assigned <span class="font-medium text-slate-800">portal credentials</span>
                    to access the ID Card Generator and other student resources.
                </p>
            </div>

            <!-- Error message -->
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

            <!-- Login card -->
            <div class="rounded-2xl bg-white shadow-md shadow-slate-200/70 border border-slate-100 px-6 py-6 sm:px-7 sm:py-7">
                <form action="login.php" method="POST" class="space-y-5">
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
                                autocomplete="current-password"
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                placeholder="Enter your password"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                class="h-3.5 w-3.5 rounded border-slate-300 text-crimson-600 focus:ring-crimson-500/70"
                            >
                            <span>Remember this device</span>
                        </label>
                        <button
                            type="button"
                            class="text-xs font-medium text-crimson-700 hover:text-crimson-800 underline underline-offset-2 decoration-crimson-200"
                        >
                            Need help?
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-lg bg-crimson-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white transition"
                    >
                        Sign in to portal
                    </button>
                </form>

                <p class="mt-5 text-[11px] leading-relaxed text-slate-500 text-center">
                    After successful login you will be redirected to the
                    <span class="font-medium text-slate-700">ID Card Generator</span> page.
                </p>
                <p class="mt-3 text-[11px] text-center text-slate-600">
                    Don’t have an account?
                    <a href="register.php" class="font-medium text-crimson-600 hover:text-crimson-700">
                        Create one
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
