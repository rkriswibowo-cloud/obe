<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SIP-OKE : Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        geist: ['Geist', 'Inter', 'sans-serif'],
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        border: 'var(--border-color)',
                        background: 'var(--bg-color)',
                        foreground: 'var(--fg-color)',
                        primary: {
                            DEFAULT: '#6366f1',
                            foreground: '#ffffff',
                        },
                        secondary: 'var(--secondary-color)',
                        muted: {
                            DEFAULT: 'var(--muted-color)',
                            foreground: 'var(--muted-fg)',
                        },
                        card: 'var(--card-color)',
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-color: #ffffff;
            --fg-color: #09090b;
            --card-color: rgba(255, 255, 255, 0.7);
            --border-color: rgba(228, 228, 231, 0.8);
            --secondary-color: #f4f4f5;
            --muted-color: #71717a;
            --muted-fg: #71717a;
        }

        html.dark, :root[data-theme="dark"] {
            --bg-color: #09090b;
            --fg-color: #f4f4f5;
            --card-color: rgba(18, 18, 20, 0.7);
            --border-color: rgba(39, 39, 42, 0.8);
            --secondary-color: #18181b;
            --muted-color: #a1a1aa;
            --muted-fg: #a1a1aa;
        }

        body {
            background-color: var(--bg-color);
            color: var(--fg-color);
            font-family: 'Geist', 'Inter', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Glass Input Wrapper */
        .glass-input-wrapper {
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            background: rgba(125, 125, 125, 0.05);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-input-wrapper:focus-within {
            border-color: rgba(139, 92, 246, 0.7);
            background: rgba(139, 92, 246, 0.08);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }

        /* Animations */
        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                filter: blur(4px);
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                filter: blur(0px);
                transform: translateY(0px);
            }
        }

        @keyframes slideRightIn {
            from {
                opacity: 0;
                filter: blur(6px);
                transform: translateX(24px);
            }
            to {
                opacity: 1;
                filter: blur(0px);
                transform: translateX(0px);
            }
        }

        @keyframes testimonialIn {
            from {
                opacity: 0;
                filter: blur(6px);
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                filter: blur(0px);
                transform: translateY(0px) scale(1);
            }
        }

        .animate-element {
            animation: fadeSlideIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .animate-slide-right {
            animation: slideRightIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .animate-testimonial {
            animation: testimonialIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .animate-delay-100 { animation-delay: 100ms; }
        .animate-delay-200 { animation-delay: 200ms; }
        .animate-delay-300 { animation-delay: 300ms; }
        .animate-delay-400 { animation-delay: 400ms; }
        .animate-delay-500 { animation-delay: 500ms; }
        .animate-delay-600 { animation-delay: 600ms; }
        .animate-delay-700 { animation-delay: 700ms; }
        .animate-delay-800 { animation-delay: 800ms; }
        .animate-delay-900 { animation-delay: 900ms; }
        .animate-delay-1000 { animation-delay: 1000ms; }
        .animate-delay-1200 { animation-delay: 1200ms; }
        .animate-delay-1400 { animation-delay: 1400ms; }

        /* Custom Checkbox */
        .custom-checkbox {
            appearance: none;
            width: 1.15rem;
            height: 1.15rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background: transparent;
            cursor: pointer;
            position: relative;
            outline: none;
            transition: all 0.15s ease;
        }
        .custom-checkbox:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
    </style>
</head>
<body class="bg-background text-foreground antialiased selection:bg-indigo-500/20 selection:text-indigo-400">

    <!-- Top Floating Theme Toggle & Back Button -->
    <div class="fixed top-6 right-6 z-50 flex items-center gap-3">
        <button type="button" onclick="toggleTheme()" class="flex items-center gap-2 px-3 py-2 rounded-xl border border-border bg-card/60 backdrop-blur-md hover:bg-secondary transition-all text-xs font-medium text-foreground/80 shadow-sm" title="Ubah Tema Gelap/Terang">
            <i id="themeToggleIcon" class="fa-solid fa-moon text-sm"></i>
            <span id="themeToggleText">Dark</span>
        </button>
    </div>

    <div class="min-h-screen flex flex-col md:flex-row font-geist w-full">
        <!-- Left column: sign-in form -->
        <section class="flex-1 flex items-center justify-center p-6 md:p-12 lg:p-16">
            <div class="w-full max-w-md">
                <div class="flex flex-col gap-6">
                    
                    <!-- Header Branding -->
                    <div class="flex items-center gap-3 animate-element animate-delay-100">
                        <div class="h-11 w-11 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 shrink-0">
                            <i class="fa-solid fa-graduation-cap text-lg"></i>
                        </div>
                        <div>
                            <span class="text-base font-bold tracking-tight text-indigo-400">SIP-OKE</span>
                            <h2 class="text-xs text-muted-foreground leading-snug">Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi</h2>
                        </div>
                    </div>

                    <div>
                        <h1 class="animate-element animate-delay-100 text-3xl md:text-5xl font-semibold leading-tight tracking-tight">
                            <span class="font-light tracking-tighter">Welcome</span> Back
                        </h1>
                        <p class="animate-element animate-delay-200 text-muted-foreground mt-2 text-sm md:text-base">
                            Masuk ke akun Anda untuk mengelola kurikulum OBE, asesmen, dan pemenuhan CPL
                        </p>
                    </div>

                    <!-- Flash Session Error Notification -->
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="animate-element animate-delay-200 flex items-center gap-3 p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                            <i class="fa-solid fa-circle-exclamation text-base"></i>
                            <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Sign In Form -->
                    <form action="/obe/public/login/submit" method="POST" class="space-y-4">
                        <div class="animate-element animate-delay-300">
                            <label class="text-xs md:text-sm font-medium text-muted-foreground block mb-2">Username / NIDN</label>
                            <div class="glass-input-wrapper">
                                <input name="username" type="text" placeholder="Enter username or NIDN" required autofocus class="w-full bg-transparent text-sm p-4 rounded-2xl focus:outline-none text-foreground placeholder:text-muted-foreground/60" />
                            </div>
                        </div>

                        <div class="animate-element animate-delay-400">
                            <label class="text-xs md:text-sm font-medium text-muted-foreground block mb-2">Password</label>
                            <div class="glass-input-wrapper">
                                <div class="relative">
                                    <input id="passwordInput" name="password" type="password" placeholder="Enter your password" required class="w-full bg-transparent text-sm p-4 pr-12 rounded-2xl focus:outline-none text-foreground placeholder:text-muted-foreground/60" />
                                    <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-3 flex items-center px-2 text-muted-foreground hover:text-foreground transition-colors">
                                        <i data-lucide="eye" id="eyeIcon" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="animate-element animate-delay-500 flex items-center justify-between text-xs md:text-sm">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="rememberMe" class="custom-checkbox" />
                                <span class="text-foreground/90 font-medium">Keep me signed in</span>
                            </label>
                            <a href="#" onclick="event.preventDefault(); alert('Silakan hubungi Administrator Prodi untuk reset kredensial Anda.');" class="hover:underline text-indigo-400 hover:text-indigo-300 transition-colors">Reset password</a>
                        </div>

                        <button type="submit" class="animate-element animate-delay-600 w-full rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] py-4 font-medium text-white transition-all shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                            <span>Sign In</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>

                    <div class="animate-element animate-delay-700 relative flex items-center justify-center my-1">
                        <span class="w-full border-t border-border"></span>
                        <span class="px-4 text-xs uppercase tracking-wider text-muted-foreground bg-background absolute">Or continue with</span>
                    </div>

                    <button type="button" onclick="alert('Fitur SSO Google Kampus terintegrasi untuk akun @kampus.ac.id');" class="animate-element animate-delay-800 w-full flex items-center justify-center gap-3 border border-border rounded-2xl py-3.5 hover:bg-secondary text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 48 48">
                            <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s12-5.373 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-2.641-.21-5.236-.611-7.743z" />
                            <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
                            <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
                            <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.022 35.026 44 30.038 44 24c0-2.641-.21-5.236-.611-7.743z" />
                        </svg>
                        <span>Continue with Google Workspace</span>
                    </button>

                    <p class="animate-element animate-delay-900 text-center text-xs md:text-sm text-muted-foreground">
                        Belum memiliki akun dosen / mahasiswa? 
                        <a href="#" onclick="event.preventDefault(); alert('Pendaftaran akun dikelola oleh Admin Program Studi.');" class="text-indigo-400 hover:underline font-medium ml-1">Hubungi Admin</a>
                    </p>

                </div>
            </div>
        </section> 

        <!-- Right column: hero image + testimonials -->
        <section class="hidden md:block flex-1 relative p-4 lg:p-6">
            <div class="animate-slide-right animate-delay-300 absolute inset-4 lg:inset-6 rounded-3xl bg-cover bg-center overflow-hidden border border-white/10 shadow-2xl" style="background-image: url('/obe/public/assets/images/gambar.png');">
                
                <!-- Gradient Vignette Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-black/20"></div>

                <!-- Top Floating Badge -->
                <div class="absolute top-8 right-8 flex items-center gap-2 px-4 py-2 rounded-full bg-black/40 backdrop-blur-md border border-white/15 text-white text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>SIP-OKE Platform &bull; Kurikulum OBE</span>
                </div>

                <!-- Bottom Floating Testimonial Cards -->
                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-4 px-6 w-full justify-center">
                    
                    <!-- Card 1 -->
                    <div class="animate-testimonial animate-delay-1000 flex items-start gap-3 rounded-3xl bg-zinc-900/60 backdrop-blur-xl border border-white/10 p-5 w-64 shadow-2xl">
                        <img src="https://cdn.21st.dev/assets/mirror/9f/9f797e4acee1a4de4f9b4c3aa1cc4e89d7c9efd5dbff1c463d88374ed601d719.jpg" class="h-10 w-10 object-cover rounded-2xl border border-white/20" alt="avatar" />
                        <div class="text-xs leading-snug">
                            <p class="flex items-center gap-1 font-semibold text-white">Rusina Widha F., M.Kom.</p>
                            <p class="text-zinc-400 text-[11px]">@kaprodi.si</p>
                            <p class="mt-1 text-zinc-300">Platform yang luar biasa! Pemantauan ketercapaian CPL dan portofolio asesmen menjadi sangat akurat.</p>
                        </div>
                    </div>

                    <!-- Card 2 (XL Screen) -->
                    <div class="hidden xl:flex animate-testimonial animate-delay-1200 items-start gap-3 rounded-3xl bg-zinc-900/60 backdrop-blur-xl border border-white/10 p-5 w-64 shadow-2xl">
                        <img src="https://cdn.21st.dev/assets/mirror/8d/8d9a61a581c43fe2088f221b7692c95db4b3ad5c0da0c856400c0e5acdcdcea8.jpg" class="h-10 w-10 object-cover rounded-2xl border border-white/20" alt="avatar" />
                        <div class="text-xs leading-snug">
                            <p class="flex items-center gap-1 font-semibold text-white">Agung Budi S., M.Pd.</p>
                            <p class="text-zinc-400 text-[11px]">@dosen_pengampu</p>
                            <p class="mt-1 text-zinc-300">Sangat memudahkan evaluasi rubrik nilai dan penyusunan RPS berbasis OBE secara terintegrasi.</p>
                        </div>
                    </div>

                    <!-- Card 3 (2XL Screen) -->
                    <div class="hidden 2xl:flex animate-testimonial animate-delay-1400 items-start gap-3 rounded-3xl bg-zinc-900/60 backdrop-blur-xl border border-white/10 p-5 w-64 shadow-2xl">
                        <img src="https://cdn.21st.dev/assets/mirror/a6/a634d4f02fe5b77804943c1d74b8d70e35ffe26454e0e9af9717432a2c72bfde.jpg" class="h-10 w-10 object-cover rounded-2xl border border-white/20" alt="avatar" />
                        <div class="text-xs leading-snug">
                            <p class="flex items-center gap-1 font-semibold text-white">Rony Kriswibowo, M.Kom.</p>
                            <p class="text-zinc-400 text-[11px]">@gugus_mutu_si</p>
                            <p class="mt-1 text-zinc-300">Siklus Continuous Quality Improvement (CQI) dan pemetaan Sub-CPMK berjalan efektif dan andal.</p>
                        </div>
                    </div>

                </div>

            </div>
        </section>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize Lucide Icons
        if (window.lucide) {
            lucide.createIcons();
        }

        // Password Reveal Toggle
        let showPassword = false;
        function togglePasswordVisibility() {
            showPassword = !showPassword;
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            
            if (input) {
                input.type = showPassword ? 'text' : 'password';
            }
            if (icon) {
                icon.setAttribute('data-lucide', showPassword ? 'eye-off' : 'eye');
                if (window.lucide) lucide.createIcons();
            }
        }

        // Theme Switcher Sync with localStorage
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.classList.remove('dark', 'light');
            document.documentElement.classList.add(theme);
            localStorage.setItem('theme', theme);

            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (icon && text) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-sun text-amber-500';
                    text.textContent = 'Light';
                } else {
                    icon.className = 'fa-solid fa-moon text-indigo-400';
                    text.textContent = 'Dark';
                }
            }
        }

        function toggleTheme() {
            const currentTheme = localStorage.getItem('theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            applyTheme(savedTheme);
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
