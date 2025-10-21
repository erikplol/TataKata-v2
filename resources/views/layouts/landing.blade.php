<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TataKata</title>
    @vite('resources/css/app.css')

</head>
<body class="bg-gray-50 text-gray-800">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <header class="flex justify-between items-center p-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <h1 class="text-2xl font-bold">TataKata</h1>
            <div>
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-full bg-white text-indigo-600 hover:bg-gray-100">Masuk</a>
                <a href="{{ route('register') }}" class="px-4 py-2 ml-2 rounded-full bg-indigo-500 hover:bg-indigo-700">Daftar</a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex flex-1 flex-col md:flex-row items-center justify-between px-10 py-20">
            <div class="md:w-1/2">
                <h2 class="text-4xl font-bold mb-4">Periksa Kata, Sempurnakan Bahasa</h2>
                <h3 class="text-5xl font-extrabold text-indigo-700 mb-6">Tata Kata.</h3>
                <p class="text-lg mb-6">
                    Tingkatkan kualitas tulisan tugas akhir Anda dengan koreksi otomatis tata bahasa,
                    ejaan, dan gaya penulisan menggunakan teknologi AI.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                    <div class="p-4 rounded-lg bg-indigo-100 text-indigo-700 text-center font-medium">
                        Pemrosesan Bahasa <br> Berbasis AI
                    </div>
                    <div class="p-4 rounded-lg bg-purple-100 text-purple-700 text-center font-medium">
                        Semua Fitur <br> Gratis
                    </div>
                    <div class="p-4 rounded-lg bg-pink-100 text-pink-700 text-center font-medium">
                        Kompatibilitas
                    </div>
                </div>
            </div>

            <!-- Logo di sebelah kanan -->
            <div class="md:w-1/2 flex justify-center mt-10 md:mt-0">
                <img src="{{ asset('images/logo-tatakata.png') }}" alt="Logo TataKata" class="w-96 h-auto">
            </div>
            
            

        </main>
    </div>
</body>
</html>
