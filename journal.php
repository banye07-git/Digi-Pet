<?php
session_start();
require_once __DIR__ . '/database.php';

// Return JSON responses for editor requests without rendering the journal page.
function sendJson($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/*
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['action'])) {
        sendJson(['error' => 'Sign in is required.'], 401);
    }

    header('Location: sign_in.php');
    exit;
}
*/

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if (!in_array($action, ['load', 'save'], true)) {
        sendJson(['error' => 'Unknown journal action.'], 404);
    }

    try {
        $database = connectDatabase();

        // Create the session-scoped entry table the first time the endpoint is used.
        $database->exec(
            'CREATE TABLE IF NOT EXISTS journal_entries (
                session_id CHAR(64) NOT NULL PRIMARY KEY,
                content LONGTEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        // Each PHP browser session gets its own entry without storing its raw session ID.
        $ownerId = hash('sha256', session_id());

        if ($action === 'load' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $statement = $database->prepare(
                'SELECT content FROM journal_entries WHERE session_id = :session_id'
            );
            $statement->execute(['session_id' => $ownerId]);
            $entry = $statement->fetch();
            sendJson(['content' => $entry ? $entry['content'] : '']);
        }

        // Save or update the entry for this session, rejecting overly large entries.
        if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $request = json_decode(file_get_contents('php://input'), true);
            if (!is_array($request) || !isset($request['content']) || !is_string($request['content'])) {
                sendJson(['error' => 'A journal entry is required.'], 400);
            }
            if (strlen($request['content']) > 5 * 1024 * 1024) {
                sendJson(['error' => 'The journal entry is too large to save.'], 413);
            }

            //insert a new row in the database table or update the existing row for this session ID.
            $statement = $database->prepare(
                'INSERT INTO journal_entries (session_id, content)
                 VALUES (:session_id, :content)
                 ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = CURRENT_TIMESTAMP'
            );

            // Execute the statement with the session ID and content from the request.
            $statement->execute([
                'session_id' => $ownerId,
                'content' => $request['content'],
            ]);
            sendJson(['saved' => true]);
        }

        sendJson(['error' => 'Method not allowed.'], 405);
    } catch (RuntimeException $error) {
        sendJson(['error' => 'Journal database is not available.'], 500);
    } catch (PDOException $error) {
        error_log('Digi-Pet journal database error: ' . $error->getMessage());
        sendJson(['error' => 'Could not connect to or save in the journal database.'], 500);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal | Digi-Pet</title>
    <!-- Load the journal's layout and editor behavior. -->
    <link rel="stylesheet" href="journal_style.css">
    <script src="journal.js" defer></script>
</head>
<body>
    <!-- Main journal page, including today's date and entry form. -->
    <main class="journal">
        <header class="journal-header">
            <p class="eyebrow">A little space for you</p>
            <h1>Journal</h1>
            <p class="journal-date" id="journal-date"></p>
        </header>

        <section class="entry-card" aria-labelledby="entry-title">
            <h2 id="entry-title">Today's entry</h2>
            <!-- Formatting controls are connected to the editable entry by journal.js. -->
            <div class="toolbar" role="toolbar" aria-label="Text formatting">
                <label class="sr-only" for="font-family">Font family</label>
                <select id="font-family" aria-label="Font family">
                    <option value="Arial">Arial</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Courier New">Courier New</option>
                </select>

                <label class="sr-only" for="font-size">Font size</label>
                <select id="font-size" aria-label="Font size">
                    <option value="3">Normal</option>
                    <option value="2">Small</option>
                    <option value="4">Large</option>
                    <option value="5">Extra large</option>
                </select>

                <span class="toolbar-divider" aria-hidden="true"></span>
                <button class="format-button" type="button" data-command="bold" aria-label="Bold" title="Bold"><strong>B</strong></button>
                <button class="format-button italic-button" type="button" data-command="italic" aria-label="Italic" title="Italic">I</button>
                <button class="format-button underline-button" type="button" data-command="underline" aria-label="Underline" title="Underline">U</button>

                <label class="color-control" title="Text color">
                    <span aria-hidden="true">A</span>
                    <input id="font-color" type="color" aria-label="Text color" value="#463c5b">
                </label>
                <span class="toolbar-divider" aria-hidden="true"></span>
                <button class="image-button" id="add-image" type="button" aria-label="Add an image">
                    <span aria-hidden="true">＋</span> Add image
                </button>
                <input id="image-input" type="file" accept="image/*" multiple hidden>
            </div>

            <!-- Contenteditable supports formatted text and inserted images. -->
            <div
                id="journal-editor"
                class="journal-editor"
                contenteditable="false"
                aria-busy="true"
                role="textbox"
                aria-label="Journal entry"
                aria-multiline="true"
                data-placeholder="Write what's on your mind... You can also paste or drop images here."
                spellcheck="true"
            ></div>
            <p class="editor-hint">Tip: paste or drag an image into your entry, or use Add image.</p>

            <!-- The script updates this status after saving the entry locally. -->
            <footer class="entry-footer">
                <p id="save-status" class="save-status" role="status" aria-live="polite"></p>
                <button id="save-entry" class="save-button" type="button" disabled>Save entry</button>
            </footer>
        </section>
    </main>
</body>
</html>