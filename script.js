"use strict";
(function () {
  const textDisplay = document.getElementById("text-display");
  const resetBtn = document.getElementById("reset-btn");
  const progressBar = document.getElementById("progress");
  const timerDisplay = document.getElementById("timer-display");
  const resultModal = document.getElementById("result-modal");
  const modalClose = document.getElementById("modal-close");
  const modalWpm = document.getElementById("modal-wpm");
  const modalAccuracy = document.getElementById("modal-accuracy");

  const TEST_TIME = 20;
  let timeRemaining = TEST_TIME;
  let testStarted = false;
  let displayingResult = false;
  let systemSaving = false;

  let quoteCharacters = [];
  let currentIndex = 0;
  let startTime = null;
  let timerInterval = null;
  let totalKeystrokes = 0; // Total characters typed (including deleted)
  let errorCount = 0; // Total errors made
  let rawCharacters = 0; // Total characters typed (including deleted and incorrect)

  function preFetchWords() {
    return fetch("get_sentence.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
      })
      .then((data) => {
        return data;
      })
      .catch((error) => {
        console.error("Error fetching sentence:", error);
        return "Error fetching sentence";
      });
  }

  async function fetchNewSentence() {
    const sentence = await preFetchWords();
    initializeQuote(sentence);
  }

  function initializeQuote(quoteText) {
    if (!quoteText) return;
    textDisplay.innerHTML = "";
    quoteCharacters = quoteText.split("");
    quoteCharacters.forEach((char) => {
      const span = document.createElement("span");
      span.textContent = char;
      textDisplay.appendChild(span);
    });
    markCurrentChar(0);
    currentIndex = 0;
    testStarted = false;
    clearInterval(timerInterval);
    startTime = null;
    totalKeystrokes = 0;
    errorCount = 0;
    rawCharacters = 0;
    timeRemaining = TEST_TIME;
    updateProgress(100);
    updateTimerDisplay(TEST_TIME);
  }

  function markCurrentChar(index) {
    const spans = textDisplay.querySelectorAll("span");
    spans.forEach((span) => span.classList.remove("current"));
    if (index < spans.length) {
      spans[index].classList.add("current");
    }

    if (spans.length <= index) {
      testStarted = false;
      displayResult();
      clearInterval(timerInterval);
      testStarted = false;
      updateTimerDisplay(TEST_TIME);
      updateProgress(100);
    }
  }

  function startTimer() {
    if (!testStarted) {
      testStarted = true;
      startTime = Date.now();
      timerInterval = setInterval(updateTimer, 100);
    }
  }

  function updateTimer() {
    const elapsedSec = (Date.now() - startTime) / 1000;
    timeRemaining = Math.max(TEST_TIME - elapsedSec, 0);
    const progressPercent = (timeRemaining / TEST_TIME) * 100;
    updateProgress(progressPercent);
    updateTimerDisplay(timeRemaining.toFixed(1));
    if (timeRemaining <= 0) {
      clearInterval(timerInterval);
      testStarted = false;
      displayResult();
    }
  }

  function updateProgress(percentage) {
    progressBar.style.width = `${percentage}%`;
  }

  function updateTimerDisplay(timeVal) {
    timerDisplay.textContent = `${timeVal}s`;
  }

  document.addEventListener("keydown", (event) => {
    if (timeRemaining <= 0 || displayingResult == true) return;

    // Track raw characters (every keypress except special keys)
    if (event.key.length === 1) {
      rawCharacters++;
    }

    if (event.key.length !== 1) {
      if (event.key === "Backspace") {
        if (currentIndex > 0) {
          currentIndex--;
          const span = textDisplay.children[currentIndex];
          span.classList.remove("correct", "incorrect");
          markCurrentChar(currentIndex);
          totalKeystrokes = Math.max(totalKeystrokes - 1, 0);
        }
      }
      return;
    }

    if (!startTime) {
      startTimer();
    }

    totalKeystrokes++;
    const expectedChar = quoteCharacters[currentIndex];
    const span = textDisplay.children[currentIndex];
    if (!span) return;

    if (event.key === expectedChar) {
      span.classList.add("correct");
    } else {
      span.classList.add("incorrect");
      errorCount++;
    }
    currentIndex++;
    markCurrentChar(currentIndex);
  });

  function displayResult() {
    displayingResult = true
    const elapsedMinutes = TEST_TIME / 60;
    const correctChars = currentIndex - errorCount;
    const wpm = elapsedMinutes > 0 ? Math.round((correctChars / 5) / elapsedMinutes) : 0;
    const accuracy = totalKeystrokes > 0 ? Math.round(((totalKeystrokes - errorCount) / totalKeystrokes) * 100) : 100;

    document.getElementById('result-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('name-input').value;
    const formData = new FormData();
    formData.append('name', name);
    formData.append('wpm', wpm);
    formData.append('accuracy', accuracy);
    // formData.append('raw', raw);
    // formData.append('errors', errors);

    try {
        systemSaving = true;
        const response = await fetch('gem_resultat.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.text();
        const feedback = document.getElementById('result-feedback');
        feedback.textContent = result;

        if (!response.ok) {
          systemSaving = false;
        }

        if (result.ok) {
            document.getElementById('name-input').disabled = true;
            document.querySelector('#result-form button').disabled = true;
            
            setTimeout(() => {
                document.getElementById('result-modal').style.display = 'none';
              systemSaving = false;
            }, 1500);
        }
    } catch (error) {
        document.getElementById('result-feedback').textContent = 'Der opstod en fejl ved gem af resultatet.';
    }
  });

    modalWpm.textContent = `Ord i minuttet: ${wpm}`;
    modalAccuracy.textContent = `Præcision: ${accuracy}%`;
    resultModal.style.display = "block";

    const resultForm = document.getElementById("result-form");
    const feedback = document.getElementById("result-feedback");

    resultForm.onsubmit = async (e) => {
      e.preventDefault();
      const name = document.getElementById("name-input").value;

      try {
        const response = await fetch("gem_resultat.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `name=${encodeURIComponent(name)}&wpm=${wpm}&accuracy=${accuracy}&raw=${rawCharacters}&errors=${errorCount}`,
        });

        const result = await response.text();
        feedback.textContent = result;
        feedback.style.color = "#d1d0c5";

        setTimeout(() => {
          feedback.textContent = "";
          resultForm.reset();
        }, 2000);
      } catch (error) {
        feedback.textContent = "Fejl ved indsendelse: " + error;
        feedback.style.color = "#ca4754";
      }
    };
  }

  modalClose.addEventListener("click", () => {
    resultModal.style.display = "none";
    displayingResult = false;
  });

  async function resetTest() {
    resetBtn.blur();
    clearInterval(timerInterval);
    updateProgress(100);
    resultModal.style.display = "none";
    await fetchNewSentence();
  }

  resetBtn.addEventListener("click", resetTest);

  fetchNewSentence();
})();