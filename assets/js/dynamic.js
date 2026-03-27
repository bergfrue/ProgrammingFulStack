const inputBox = document.getElementById("messageBox");
const charCount = document.getElementById("charCount");
const resultBox = document.getElementById("resultBox");
const loader = document.getElementById("loader");
const popup = document.getElementById("popup");

// Character counter
inputBox.addEventListener("keyup", () => {
    const count = inputBox.value.length;
    charCount.textContent = count + " characters";
});

// Classify button click
document.getElementById("classifyBtn").addEventListener("click", async () => {

    resultBox.innerHTML = "";
    loader.style.display = "block";

    const text = inputBox.value.trim();

    if (text === "") {
        loader.style.display = "none";
        showPopup("Please enter a message to classify.");
        return;
    }

    const formData = new FormData();
    formData.append("message", text);

    try {
        const response = await fetch("backend/classify.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();
        loader.style.display = "none";

        if (data.error) {
            showPopup(data.error);
            return;
        }

        let badge = "";

        if (data.classification === "Spam") {
            badge = `<span class="badge spam">Spam (Score: ${data.score.toFixed(2)})</span>`;
        } else {
            badge = `<span class="badge ham">Ham (Score: ${data.score.toFixed(2)})</span>`;
        }

        resultBox.innerHTML = `
            <h3>Classification Result</h3>
            <p>${badge}</p>
            <p><strong>Message:</strong> ${data.text}</p>
        `;

        showPopup("Classification complete!");

    } catch (err) {
        loader.style.display = "none";
        showPopup("There was an error processing your request.");
    }
});

// Popup message
function showPopup(message) {
    popup.textContent = message;
    popup.classList.add("visible");

    setTimeout(() => {
        popup.classList.remove("visible");
    }, 3000);
}

// Fade-in animation for updated results
const observer = new MutationObserver(() => {
    resultBox.classList.add("fadeIn");

    setTimeout(() => {
        resultBox.classList.remove("fadeIn");
    }, 1000);
});

observer.observe(resultBox, { childList: true });
