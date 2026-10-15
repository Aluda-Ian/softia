<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1E3A8A">
    <title>You're offline · Softia</title>
    {{-- Self-contained: this page is cached and shown with no connection. --}}
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: linear-gradient(#C9DCF3, #F8FAFC); font-family: Montserrat, system-ui, sans-serif; color: #1E3A8A; text-align: center; padding: 1.5rem; }
        img { width: 11rem; filter: drop-shadow(0 18px 20px rgb(30 58 138 / .2)); }
        h1 { margin: 1.5rem 0 .5rem; font-size: 1.75rem; }
        p { margin: 0 auto; max-width: 22rem; opacity: .75; line-height: 1.6; }
        .wave { position: fixed; left: 0; right: 0; bottom: 0; width: 100%; height: 18vh; color: #1E3A8A; }
        main { position: relative; z-index: 1; }
        button { margin-top: 1.75rem; border: 0; border-radius: 999px; background: #1E3A8A; color: #fff; padding: .9rem 1.75rem; font: inherit; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <main>
        <img src="/images/cutouts/roll-single.png" alt="">
        <h1>You're offline</h1>
        <p>Check your internet connection and try again. Your cart is saved.</p>
        <button type="button" onclick="location.reload()">Try again</button>
    </main>
    <svg class="wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path fill="currentColor" d="M0 56C240 16 520 0 820 34C1040 59 1180 104 1440 76V120H0Z" /></svg>
</body>
</html>
