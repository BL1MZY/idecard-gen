<?php
// index.php
require __DIR__ . '/auth_check.php';   // 👈 keep auth check at the top
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>De Switch Tech - ID Card Generator</title>

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

            <!-- Breadcrumb / Page Heading -->
            <div class="mb-6 sm:mb-8">
                <nav class="text-xs text-slate-500 mb-2">
                    <ol class="flex items-center gap-1">
                        <li>Dashboard</li>
                        <li class="text-slate-400">/</li>
                        <li class="font-medium text-slate-700">ID Card Generator</li>
                    </ol>
                </nav>
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">
                            ID Card Generator
                        </h1>
                        <p class="mt-1 text-sm text-slate-600 max-w-xl">
                            Generate an official De Switch Tech identification card with your details and photo.
                            Please ensure all information is accurate and up to date.
                        </p>
                    </div>
                    <div class="inline-flex items-center rounded-full border border-crimson-100 bg-crimson-50 px-3 py-1 text-[11px] font-medium text-crimson-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-crimson-500 mr-2"></span>
                        Academic Records & Identity Services
                    </div>
                </div>
            </div>

            <!-- Layout: Form Card + Info panel -->
            <div class="grid gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                <!-- Form Card -->
                <section class="rounded-2xl bg-white border border-slate-100 shadow-sm shadow-slate-200/60">
                    <div class="px-5 py-5 sm:px-6 sm:py-6 border-b border-slate-100">
                        <h2 class="text-base sm:text-lg font-semibold text-slate-900">
                            ID Information
                        </h2>
                        <p class="mt-1 text-xs sm:text-sm text-slate-500">
                            Fields marked with <span class="text-crimson-600">*</span> are required.
                        </p>
                    </div>

                    <div class="px-5 py-5 sm:px-6 sm:py-6">
                        <form action="generate.php" method="POST" enctype="multipart/form-data" class="space-y-5">

                            <!-- Full Name -->
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-slate-800">
                                    Full Name <span class="text-crimson-600">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <input
                                        type="text"
                                        name="full_name"
                                        id="full_name"
                                        required
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                        placeholder="e.g. Jane Doe"
                                    >
                                </div>
                            </div>

                            <!-- Role / Position -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-slate-800">
                                    Role / Position <span class="text-crimson-600">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <input
                                        type="text"
                                        name="role"
                                        id="role"
                                        required
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                        placeholder="e.g. Student, Instructor, Staff"
                                    >
                                </div>
                            </div>

                            <!-- Department -->
                            <div>
                                <label for="department" class="block text-sm font-medium text-slate-800">
                                    Department <span class="text-crimson-600">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <input
                                        type="text"
                                        name="department"
                                        id="department"
                                        required
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                        placeholder="e.g. Computer Science, Admin Office"
                                    >
                                </div>
                            </div>

                            <!-- Employee / Student ID -->
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-slate-800">
                                    Employee / Student ID <span class="text-crimson-600">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <input
                                        type="text"
                                        name="employee_id"
                                        id="employee_id"
                                        required
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                        placeholder="e.g. DST-2024-00123"
                                    >
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-slate-800">
                                    Expiry Date <span class="text-crimson-600">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <input
                                        type="date"
                                        name="expiry_date"
                                        id="expiry_date"
                                        required
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-crimson-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-crimson-500/60"
                                    >
                                </div>
                            </div>

                            <!-- Photo Upload -->
                            <div>
                                <label for="photo" class="block text-sm font-medium text-slate-800">
                                    Photo (JPG or PNG) <span class="text-crimson-600">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <div class="flex items-center gap-4">
                                        <label class="flex flex-1 items-center justify-between rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 text-xs sm:text-sm text-slate-600 cursor-pointer hover:border-crimson-400 hover:bg-crimson-50/40 transition">
                                            <span class="truncate">
                                                Choose a recent, passport-style photo. Maximize clarity and neutral background.
                                            </span>
                                            <span class="ml-3 inline-flex items-center rounded-md bg-white px-3 py-1 text-xs font-medium text-slate-700 border border-slate-200 shadow-sm">
                                                Browse…
                                            </span>
                                            <input
                                                type="file"
                                                name="photo"
                                                id="photo"
                                                accept="image/jpeg,image/png"
                                                required
                                                class="hidden"
                                            >
                                        </label>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Accepted formats: JPG, PNG. Recommended size: 400×400px or higher.
                                    </p>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-crimson-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-crimson-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-crimson-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-50 transition"
                                >
                                    Generate ID Card
                                </button>
                            </div>
                        </form>

                        <p class="mt-4 text-[11px] text-slate-500 leading-relaxed">
                            After submitting, your ID card will be generated and displayed on the next page.
                            Review carefully before printing or sharing.
                        </p>
                    </div>
                </section>

                <!-- Side Info / Guidelines Panel -->
                <aside class="space-y-4">
                    <div class="rounded-2xl bg-crimson-900 text-crimson-50 px-5 py-4 sm:px-6 sm:py-5 shadow-sm">
                        <h3 class="text-sm font-semibold tracking-tight flex items-center gap-2">
                            ID Card Guidelines
                        </h3>
                        <ul class="mt-3 space-y-2 text-xs leading-relaxed text-crimson-50/90">
                            <li>• Use your <span class="font-semibold">official name</span> as it appears in school records.</li>
                            <li>• Choose a <span class="font-semibold">clear, recent, passport-style</span> photograph.</li>
                            <li>• Ensure your <span class="font-semibold">department and ID</span> are correct to avoid reprints.</li>
                            <li>• Expiry date typically aligns with your <span class="font-semibold">program or contract duration</span>.</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl bg-white border border-slate-100 px-5 py-4 sm:px-6 sm:py-5 shadow-sm text-xs text-slate-600 space-y-2">
                        <h3 class="text-sm font-semibold text-slate-900">
                            Data Protection Notice
                        </h3>
                        <p>
                            Information submitted here is used solely for identity verification and official
                            De Switch Tech records, in line with institutional data protection policies.
                        </p>
                        <p class="text-[11px] text-slate-500">
                            For corrections or support, contact the ICT office or your department administrator.
                        </p>
                    </div>
                </aside>
            </div>
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
