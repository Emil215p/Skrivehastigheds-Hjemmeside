const textDisplay = document.getElementById('text-display');
const wpmElement = document.getElementById('wpm');
const accuracyElement = document.getElementById('accuracy');
const resetBtn = document.getElementById('reset-btn');

let quoteCharacters = [];
let currentIndex = 0;
let startTime = null;
let timerInterval = null;
let isTestActive = false;
let nextSentence = null;
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
      return 'fejl ved hentning af ord';
    });
}

async function fetchNewSentence() {
  if (!nextSentence) {
    nextSentence = await preFetchWords();
  }
  initializeQuote(nextSentence);
  preFetchWords().then(sentence => {
    nextSentence = sentence;
  });
}

function initializeQuote(quoteText) {
  if (!quoteText) {
    console.error('initializeQuote: quoteText is undefined or empty.');
    return;
  }
  textDisplay.innerHTML = '';
  quoteCharacters = quoteText.split('');
  quoteCharacters.forEach(char => {
    const span = document.createElement('span');
    span.textContent = char;
    textDisplay.appendChild(span);
  });
  markCurrentChar(0);
  currentIndex = 0;
  isTestActive = false;
  if (timerInterval) clearInterval(timerInterval);
  startTime = null;
  totalKeystrokes = 0;
  errorCount = 0;
  updateAccuracy();
}

function markCurrentChar(index) {
  const spans = textDisplay.querySelectorAll('span');
  spans.forEach(span => span.classList.remove('current'));
  if (index < spans.length) {
    spans[index].classList.add('current');
  }
}

function startTimer() {
  if (!isTestActive) {
    isTestActive = true;
    startTime = Date.now();
    timerInterval = setInterval(updateWPM, 1000);
  }
}

function updateWPM() {
  const correctCount = totalKeystrokes - errorCount;
  const elapsedTime = (Date.now() - startTime) / 1000 / 60;
  const wpm = elapsedTime > 0 ? Math.round(correctCount / 5 / elapsedTime) : 0;
  wpmElement.textContent = wpm;
}

function updateAccuracy() {
  const accuracy = totalKeystrokes > 0 ? Math.round(((totalKeystrokes - errorCount) / totalKeystrokes) * 100) : 0;
  accuracyElement.textContent = accuracy;
}

function handleKeyDown(event) {
  if (!isTestActive) {
    startTimer();
  }

  if (event.key === "Backspace") {
    if (currentIndex > 0) {
      currentIndex--;
      const span = textDisplay.children[currentIndex];
      span.classList.remove("correct", "incorrect");
      markCurrentChar(currentIndex);
    }
    updateAccuracy();
    return;
  }

  if (event.key.length !== 1) return;

  totalKeystrokes++;
  const expectedChar = quoteCharacters[currentIndex];
  const span = textDisplay.children[currentIndex];

  if (event.key === expectedChar) {
    span.classList.add("correct");
  } else {
    span.classList.add("incorrect");
    errorCount++;
  }
  
  span.dataset.user = event.key;
  currentIndex++;
  markCurrentChar(currentIndex);
  updateAccuracy();
  updateWPM();

  if (currentIndex === quoteCharacters.length) {
    clearInterval(timerInterval);
    isTestActive = false;
  }
}


document.addEventListener('keydown', handleKeyDown);

resetBtn.addEventListener('click', async () => {
  clearInterval(timerInterval);
  wpmElement.textContent = 0;
  accuracyElement.textContent = 0;
  await fetchNewSentence();
});

preFetchWords().then(sentence => {
  nextSentence = sentence;
  fetchNewSentence();
});
