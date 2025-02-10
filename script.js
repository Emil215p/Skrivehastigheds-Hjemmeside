"use strict";
(function() {
  const textDisplay = document.getElementById('text-display');
  const resetBtn = document.getElementById('reset-btn');
  const progressBar = document.getElementById('progress');
  const timerDisplay = document.getElementById('timer-display');
  const resultModal = document.getElementById('result-modal');
  const modalClose = document.getElementById('modal-close');
  const modalWpm = document.getElementById('modal-wpm');
  const modalAccuracy = document.getElementById('modal-accuracy');

  const TEST_TIME = 30; // seconds
  let timeRemaining = TEST_TIME;
  let testStarted = false;

  let quoteCharacters = [];
  let currentIndex = 0;
  let startTime = null;
  let timerInterval = null;
  let totalKeystrokes = 0;
  let errorCount = 0;

  function preFetchWords() {
    return fetch('words.txt')
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
      })
      .then(data => {
        const words = data.split('\n')
          .map(word => word.trim())
          .filter(word => word.length > 0);
        const selectedWords = [];
        const count = 30;
        for (let i = 0; i < count; i++) {
          const randomIndex = Math.floor(Math.random() * words.length);
          selectedWords.push(words[randomIndex]);
        }
        return selectedWords.join(' ');
      })
      .catch(error => {
        console.error('Error fetching words.txt:', error);
        return 'Error fetching words';
      });
  }

  async function fetchNewSentence() {
    const sentence = await preFetchWords();
    initializeQuote(sentence);
  }

  function initializeQuote(quoteText) {
    if (!quoteText) return;
    textDisplay.innerHTML = '';
    quoteCharacters = quoteText.split('');
    quoteCharacters.forEach(char => {
      const span = document.createElement('span');
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
    timeRemaining = TEST_TIME;
    updateProgress(100);
    updateTimerDisplay(TEST_TIME);
  }

  function markCurrentChar(index) {
    const spans = textDisplay.querySelectorAll('span');
    spans.forEach(span => span.classList.remove('current'));
    if (index < spans.length) {
      spans[index].classList.add('current');
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

  document.addEventListener('keydown', (event) => {
    if (timeRemaining <= 0) return;
    
    if (event.key.length !== 1) {
      if (event.key === 'Backspace') {
        if (currentIndex > 0) {
          currentIndex--;
          const span = textDisplay.children[currentIndex];
          span.classList.remove('correct', 'incorrect');
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
      span.classList.add('correct');
    } else {
      span.classList.add('incorrect');
      errorCount++;
    }
    currentIndex++;
    markCurrentChar(currentIndex);
  });

  function displayResult() {
    const elapsedMinutes = TEST_TIME / 60;
    const correctChars = currentIndex - errorCount;
    const wpm = elapsedMinutes > 0 ? Math.round((correctChars / 5) / elapsedMinutes) : 0;
    const accuracy = totalKeystrokes > 0 ? Math.round(((totalKeystrokes - errorCount) / totalKeystrokes) * 100) : 100;
    modalWpm.textContent = `Ord i minutet: ${wpm}`;
    modalAccuracy.textContent = `Præcision: ${accuracy}%`;
    resultModal.style.display = "block";
  }

  modalClose.addEventListener('click', () => {
    resultModal.style.display = "none";
  });

  async function resetTest() {
    clearInterval(timerInterval);
    updateProgress(100);
    resultModal.style.display = "none";
    await fetchNewSentence();
  }

  resetBtn.addEventListener('click', resetTest);

  fetchNewSentence();
})();
