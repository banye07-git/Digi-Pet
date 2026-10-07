const journal = document.createElement("div");
journal.className = "journal";
const journalLabel = document.createElement("label");
journalLabel.textContent = "Journal Entry:";
journalLabel.setAttribute("for", "journal");

const journalheader = document.createElement("h2");
journalheader.textContent = "Journal Entry";
const journalDate = document.createElement("p");
journalDate.textContent = new Date().toLocaleDateString();

const journalInput = document.createElement("textarea");
journalInput.id = "journal";
journalInput.name = "journal";

const journalSubmit = document.createElement("button");
journalSubmit.type = "submit";
journalSubmit.textContent = "Submit";