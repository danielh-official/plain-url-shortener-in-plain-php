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
            <p class="bg-ok p rounded m-0 flex-1">Your short link: <a target="_blank"
                    href="<?= e($baseUrl . '/' . $created['code']) ?>">
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $link): ?>
                    <tr>
                        <td><a target="_blank" href="/<?= e($link['code']) ?>"><?= e($link['code']) ?></a></td>
                        <td class="truncate"><?= e($link['url']) ?></td>
                        <td><?= (int) $link['clicks'] ?></td>
                        <td>
                            <form method="post" action="/<?= e($link['code']) ?>/delete">
                                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                <button type="submit" class="text-error cursor-pointer"
                                    onclick="return confirm('Are you sure you want to delete this link?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-trash-2">
                                        <path d="M3 6h18" />
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>