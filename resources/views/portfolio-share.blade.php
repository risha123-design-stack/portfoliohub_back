<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $title }}</title>

    <meta
        name="description"
        content="{{ $description }}"
    >

    <meta
        name="robots"
        content="{{ $allowSearchEngines
            ? 'index, follow'
            : 'noindex, nofollow' }}"
    >

    <link
        rel="canonical"
        href="{{ $canonicalUrl }}"
    >

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:site_name"
        content="PortfolioHub"
    >

    <meta
        property="og:title"
        content="{{ $title }}"
    >

    <meta
        property="og:description"
        content="{{ $description }}"
    >

    <meta
        property="og:image"
        content="{{ $imageUrl }}"
    >

    <meta
        property="og:url"
        content="{{ $canonicalUrl }}"
    >

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="{{ $title }}"
    >

    <meta
        name="twitter:description"
        content="{{ $description }}"
    >

    <meta
        name="twitter:image"
        content="{{ $imageUrl }}"
    >

    <meta
        http-equiv="refresh"
        content="0;url={{ $portfolioUrl }}"
    >

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            box-sizing: border-box;
            background: #f7f8fc;
            color: #171a2b;
            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .share-card {
            width: min(520px, 100%);
            padding: 40px 32px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #e7e9f1;
            border-radius: 22px;
            box-shadow:
                0 24px 60px
                rgba(24, 30, 60, 0.10);
        }

        .share-card h1 {
            margin: 0 0 12px;
            font-size: clamp(28px, 5vw, 42px);
        }

        .share-card p {
            margin: 0 0 24px;
            color: #697086;
            line-height: 1.7;
        }

        .share-card a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 20px;
            border-radius: 12px;
            background: #6c5ce7;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <main class="share-card">
        <h1>Opening portfolio...</h1>

        <p>
            You will be redirected automatically.
            If nothing happens, use the button below.
        </p>

        <a href="{{ $portfolioUrl }}">
            Open portfolio
        </a>
    </main>

    <script>
        window.location.replace(
            @json($portfolioUrl)
        );
    </script>
</body>
</html>