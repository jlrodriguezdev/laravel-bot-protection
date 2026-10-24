<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification | Bot Protection</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="max-w-md w-full bg-gray-900 border border-gray-800 rounded-2xl p-8 text-center shadow-2xl">
        <div class="w-16 h-16 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold animate-pulse">
            🔍
        </div>
        <h1 class="text-xl font-bold mb-2">Verifying your browser...</h1>
        <p class="text-sm text-gray-400 mb-6">
            Please wait a moment while we verify the security of your connection.
        </p>
        <script>
            setTimeout(function() {
                document.cookie = "verified_browser=1; path=/; max-age=86400; SameSite=Lax";
                window.location.href = "{{ $redirectTo ?? '/' }}";
            }, 1000);
        </script>
        <div class="text-xs text-gray-500 border-t border-gray-800 pt-4">
            Protected by <span class="text-gray-300 font-semibold">Laravel BotProtection</span>
        </div>
    </div>
</body>
</html>
