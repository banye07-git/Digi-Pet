const editor = document.getElementById("journal-editor");
const imageInput = document.getElementById("image-input");
const saveStatus = document.getElementById("save-status");
const saveButton = document.getElementById("save-entry");
let savedSelection = null;

// Initialize the page's date label using the visitor's local time.
document.getElementById("journal-date").textContent = new Date().toLocaleDateString(
    undefined,
    { year: "numeric", month: "long", day: "numeric" }
);

// Decode JSON responses and report plain-text/server error pages without a JSON parse error.
async function readJsonResponse(response) {
    const responseText = await response.text();
    try {
        return JSON.parse(responseText);
    } catch (error) {
        const details = responseText.trim().replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").slice(0, 200);
        if (!response.ok && details) {
            throw new Error(`Server error (${response.status}): ${details}`);
        }
        throw new Error(`Server returned an invalid response (HTTP ${response.status}).`);
    }
}

// Remember the text cursor position or highlighted text while toolbar controls are used.
function rememberSelection() {
    const selection = window.getSelection();
    // Keep only selections inside the editor so formatting applies to the entry.
    if (selection && selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
        savedSelection = selection.getRangeAt(0).cloneRange();
    }
}

// Focus the editor and restore where typing should continue before applying an edit.
function restoreSelection() {
    editor.focus();
    // Restore the text cursor or highlighted text after toolbar controls take focus.
    if (savedSelection && editor.contains(savedSelection.commonAncestorContainer)) {
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(savedSelection);
    }
}

// Apply a browser text-editing command (font, size, style, or color) to the selection.
function applyCommand(command, value = null) {
    restoreSelection();
    document.execCommand(command, false, value);
    rememberSelection();
}

// Read an image as embedded data and insert it where the text cursor was last positioned.
function insertImage(file) {
    // Reject non-image files before reading them into the entry.
    if (!file.type.startsWith("image/")) {
        saveStatus.textContent = "Please choose an image file.";
        return;
    }

    // Read the image file and verify that the browser produced usable image data.
    const reader = new FileReader();
    reader.addEventListener("load", () => {
        if (typeof reader.result !== "string") {
            saveStatus.textContent = "That image couldn't be added.";
            return;
        }

        restoreSelection();
        // Embed the image as a data URL so it is included when the entry is saved.
        const image = document.createElement("img");
        image.src = reader.result;
        image.alt = file.name ? `Journal image: ${file.name}` : "Journal image";
        document.execCommand("insertHTML", false, image.outerHTML);
        rememberSelection();
        saveStatus.textContent = "Image added to your entry.";
    });
    reader.addEventListener("error", () => {
        saveStatus.textContent = "That image couldn't be read.";
    });
    reader.readAsDataURL(file);
}

// Track selection changes and clear old save feedback when the entry is edited.
document.addEventListener("selectionchange", rememberSelection);
editor.addEventListener("input", () => {
    saveStatus.textContent = "";
    rememberSelection();
});

// Connect the font family, size, style, and color controls to the selected text.
document.getElementById("font-family").addEventListener("change", (event) => {
    applyCommand("fontName", event.target.value);
});

document.getElementById("font-size").addEventListener("change", (event) => {
    applyCommand("fontSize", event.target.value);
});

// Keep the editor selection when a style button is clicked, then toggle its text format.
document.querySelectorAll("[data-command]").forEach((button) => {
    button.addEventListener("mousedown", (event) => event.preventDefault());
    button.addEventListener("click", () => applyCommand(button.dataset.command));
});

document.getElementById("font-color").addEventListener("input", (event) => {
    applyCommand("foreColor", event.target.value);
});

// Keep the editor's insertion position and open the file picker when Add image is clicked.
document.getElementById("add-image").addEventListener("mousedown", (event) => event.preventDefault());
document.getElementById("add-image").addEventListener("click", () => {
    rememberSelection();
    imageInput.click();
});

// Insert all chosen image files and clear the picker so the same file can be chosen again.
imageInput.addEventListener("change", () => {
    Array.from(imageInput.files).forEach(insertImage);
    imageInput.value = "";
});

// Intercept pasted images and embed them in the journal instead of pasting a file reference.
editor.addEventListener("paste", (event) => {
    const images = Array.from(event.clipboardData.files).filter((file) => file.type.startsWith("image/"));
    if (images.length > 0) {
        event.preventDefault();
        images.forEach(insertImage);
    }
});

// Allow browser file drops by preventing the default file-navigation behavior.
editor.addEventListener("dragover", (event) => {
    if (Array.from(event.dataTransfer.types).includes("Files")) {
        event.preventDefault();
    }
});

// Insert dropped images where the text cursor was last positioned; leave other drops unchanged.
editor.addEventListener("drop", (event) => {
    const images = Array.from(event.dataTransfer.files).filter((file) => file.type.startsWith("image/"));
    if (images.length > 0) {
        event.preventDefault();
        rememberSelection();
        images.forEach(insertImage);
    }
});

// Fetch the entry associated with this PHP session and report server/load errors in the page.
async function loadEntry() {
    saveStatus.textContent = "Loading saved entry...";
    try {
        const response = await fetch("journal.php?action=load", {
            headers: { Accept: "application/json" },
            credentials: "same-origin",
        });
        const result = await readJsonResponse(response);
        if (!response.ok) {
            throw new Error(result.error || "Could not load the saved entry.");
        }
        if (typeof result.content !== "string") {
            throw new Error("The server returned an invalid journal entry.");
        }
        editor.innerHTML = result.content;
        saveStatus.textContent = "";
        saveButton.disabled = false;
    } catch (error) {
        saveStatus.textContent = `${error.message} Check the database settings, then reload this page.`;
    } finally {
        editor.contentEditable = "true";
        editor.setAttribute("aria-busy", "false");
    }
}

// Send the complete formatted editor HTML to the PHP endpoint for database storage.
saveButton.addEventListener("click", async () => {
    saveButton.disabled = true;
    saveStatus.textContent = "Saving entry...";
    try {
        const response = await fetch("journal.php?action=save", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            credentials: "same-origin",
            body: JSON.stringify({ content: editor.innerHTML }),
        });
        const result = await readJsonResponse(response);
        if (!response.ok) {
            throw new Error(result.error || "Could not save the entry.");
        }
        saveStatus.textContent = "Entry saved to the database.";
    } catch (error) {
        saveStatus.textContent = error.message;
    } finally {
        saveButton.disabled = false;
    }
});

// Restore the saved entry as soon as the page's controls are ready.
loadEntry();
