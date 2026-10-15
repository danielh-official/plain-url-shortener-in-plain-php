<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shortener</title>
    <style>
        body {
            font: 16px/1.5 system-ui, sans-serif;
            max-width: 40rem;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        form {
            display: flex;
            gap: .5rem;
        }

        input[type=url] {
            flex: 1;
            padding: .5rem;
        }

        button {
            padding: .5rem 1rem;
        }

        .error {
            color: #b00020;
        }

        .ok {
            background: #e8f5e9;
            padding: .75rem 1rem;
            border-radius: .25rem;
            margin: 0;
            flex: 1;
        }

        .created {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 1rem 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th,
        td {
            text-align: left;
            padding: .4rem .25rem;
            border-bottom: 1px solid #ddd;
        }

        td.url {
            max-width: 18rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <h1>Shortener</h1>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($created): ?>
        <div class="created">
            <p class="ok">Your short link: <a target="_blank" href="<?= e($baseUrl . '/' . $created['code']) ?>">
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
                        <td class="url"><?= e($link['url']) ?></td>
                        <td><?= (int) $link['clicks'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>