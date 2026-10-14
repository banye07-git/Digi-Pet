const noteContainer = document.getElementById("Note");


const form = document.createElement("form");
form.method = "POST";
form.action = "/Submit";

const journal = document.createElement("div");
journal.className = "journal";
const journalLabel = document.createElement("label");
//journalLabel.textContent = "Journal Entry:";
//journalLabel.ariaPlaceholder = "Write your journal entry here...";
journalLabel.setAttribute("for", "journal");

const journalheader = document.createElement("h2");
journalheader.textContent = "Journal Entry";

const journalDate = document.createElement("p");
journalDate.textContent = new Date().toLocaleDateString();

const fontcontrol = document.createElement("div");
fontcontrol.className = "font-control";
// create a select element for font control
const fontstylecontrol = document.createElement("select");
fontstylecontrol.innerHTML = `
    <option value="Arial">Arial</option>
    <option value="Verdana">Verdana</option>
    <option value="Times New Roman">Times New Roman</option>
`;



fontstylecontrol.addEventListener("change", () => {
    document.execCommand("fontName", false, fontstylecontrol.value);
    // journalInput.style.fontFamily = fontstylecontrol.value; 
});

// create a select element for font weight control
const fontweightcontrol = document.createElement("select");
fontweightcontrol.innerHTML = `
    <option value="" hidden>Font</option>
    <option value="normal">Normal</option>
    <option value="bold">Bold</option>
    <option value="lighter">Lighter</option>
`;
fontweightcontrol.addEventListener("change", () => {
    document.execCommand("bold", false, fontweightcontrol.value);
    // journalInput.style.fontWeight = fontweightcontrol.value;
});

// create a button to underline the journal entry text
const underlineControl = document.createElement("button");
underlineControl.innerHTML = "U";
underlineControl.addEventListener("click", () => {
    document.execCommand("underline", false, null);
    // journalInput.style.textDecoration = journalInput.style.textDecoration === "underline" ? "none" : "underline";
});

// change the font color of the journal entry text
const fontcolorControl = document.createElement("input");
fontcolorControl.type = "color";
fontcolorControl.addEventListener("change", () => {
    document.execCommand("foreColor", false, fontcolorControl.value);
    // journalInput.style.color = fontcolorControl.value;
});

//Appending all font style elements to the font control div
fontcontrol.appendChild(fontstylecontrol);
fontcontrol.appendChild(fontweightcontrol);
fontcontrol.appendChild(underlineControl);
fontcontrol.appendChild(fontcolorControl);


//changed the textarea to a contenteditable div in order to allow for styling of the text within the journal entry. 
// This allows for more flexibility in formatting and styling the text, such as changing the font, weight, color, 
// and adding underline. The contenteditable attribute makes the div editable by the user, similar to a textarea, 
// but with more styling options.
const journalInput = document.createElement("contenteditable");
journalInput.id = "journal";
journalInput.contentEditable = "true";



const journalSubmit = document.createElement("button");
journalSubmit.type = "submit";
journalSubmit.textContent = "Save Entry";



journal.appendChild(journalheader);
journal.appendChild(journalDate);
journal.appendChild(journalLabel); // Add the label to the journal div
journal.appendChild(fontcontrol);
journal.appendChild(journalInput);
journal.appendChild(journalSubmit);
form.appendChild(journal); // Add the journal div to the form
noteContainer.appendChild(form); // Add the form to the noteContainer