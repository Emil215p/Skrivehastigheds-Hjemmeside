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

let testMode = 'words';
let testTime = 30; // seconds
let wordCount = 25;

async function resetTest() {
  clearInterval(timerInterval);
  wpmElement.textContent = 0;
  accuracyElement.textContent = 100;
  await fetchNewSentence();
  currentIndex = 0;
  startTime = null;
  isTestActive = false;
  totalKeystrokes = 0;
  errorCount = 0;
}

document.querySelectorAll('.mode-btn').forEach(btn => {
  btn.addEventListener('click', async () => { // Make async
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    testMode = btn.dataset.mode;
    await resetTest(); // Add await
  });
});

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
      return 'error fetching words';
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
    console.error('No quote text available.');
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
  clearInterval(timerInterval);
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
  if (!startTime) return;
  
  const elapsedSeconds = (Date.now() - startTime) / 1000;
  const correctCharacters = currentIndex - errorCount;
  
  let wpm;
  if (testMode === 'time') {
    wpm = Math.round((correctCharacters / 5) / (elapsedSeconds / 60));
  } else {
    const wordCount = textDisplay.textContent.split(' ').length;
    wpm = Math.round((wordCount / elapsedSeconds) * 60);
  }
  
  wpmElement.textContent = wpm;
}


function updateAccuracy() {
  const accuracy = totalKeystrokes > 0 ? Math.round(((totalKeystrokes - errorCount) / totalKeystrokes) * 100) : 100;
  accuracyElement.textContent = accuracy;
}

function createProgressBar() {
  const progress = document.createElement('div');
  progress.id = 'progress';
  document.body.appendChild(progress);
  
  return () => {
    const progressWidth = (currentIndex / quoteCharacters.length) * 100;
    progress.style.width = `${progressWidth}%`;
  };
}
const updateProgress = createProgressBar();

const themes = {
  dark: {
    '--background-color': '#1c1c1c',
    '--correct-color': '#d1d0c5',
  },
  light: {
    '--background-color': '#ffffff',
    '--correct-color': '#2a2a2a',
  }
};

function setTheme(themeName) {
  const theme = themes[themeName];
  Object.entries(theme).forEach(([varName, value]) => {
    document.documentElement.style.setProperty(varName, value);
  });
}

function handleKeyDown(event) {
  // Start timer on first keystroke
  if (!isTestActive) {
    startTimer();
  }
  // Allow backspace for corrections
  if (event.key === 'Backspace') {
    if (currentIndex > 0) {
      currentIndex--;
      const span = textDisplay.children[currentIndex];
      span.classList.remove('correct', 'incorrect');
      markCurrentChar(currentIndex);
    }
    updateAccuracy();
    return;
  }
  // Process only single character keys
  if (event.key.length !== 1) return;

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
  span.dataset.user = event.key;
  currentIndex++;
  markCurrentChar(currentIndex);
  updateAccuracy();
  updateWPM();
  updateProgress();

  if (currentIndex === quoteCharacters.length) {
    clearInterval(timerInterval);
    isTestActive = false;
  }
}

document.addEventListener('keydown', handleKeyDown);

resetBtn.addEventListener('click', async () => {
  clearInterval(timerInterval);
  wpmElement.textContent = 0;
  accuracyElement.textContent = 100;
  await fetchNewSentence();
});

preFetchWords().then(sentence => {
  nextSentence = sentence;
  fetchNewSentence();
});
