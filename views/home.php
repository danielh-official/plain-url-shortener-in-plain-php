<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shortener</title>
    <link rel="stylesheet" href="/app.css">
</head>

<body>
    <h1>Shortener</h1>

    <?php if ($error): ?>
        <p class="text-error"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($created): ?>
        <div class="flex items-center gap my">
            <p class="bg-ok p rounded m-0 flex-1">Your short link: <a target="_blank" href="<?= e($baseUrl . '/' . $created['code']) ?>">
                    <?= e("$baseUrl/{$created['code']}") ?>
                </a></p>
            <a href="/">Clear</a>
        </div>
    <?php endif; ?>

    <form method="post" action="/shorten">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="url" name="url" placeholder="https://example.com/a/very/long/url" required>
        <button type="submit">Shorten</button>
    </form>

    <?php if ($recent): ?>
        <h2>Recent links</h2>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Destination</th>
                    <th>Clicks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $link): ?>
                    <tr>
                        <td><a target="_blank" href="/<?= e($link['code']) ?>"><?= e($link['code']) ?></a></td>
                        <td class="truncate"><?= e($link['url']) ?></td>
                        <td><?= (int) $link['clicks'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>
