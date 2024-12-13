// script.js
document.addEventListener("DOMContentLoaded", function () {
  fetch("get_username.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.username) {
        document.getElementById(
          "username_display"
        ).innerText = `Welcome ${data.username}`;
      }
      if (data.chips) {
        document.getElementById(
          "user_chips"
        ).innerText = `Chips: ${data.chips}`;
        // Set the max attribute for bet_amount
        const betAmountInput = document.getElementById("bet_amount");
        if (betAmountInput) {
          betAmountInput.max = data.chips;
        }
      }
    })
    .catch((error) => {
      console.error("Error fetching user data:", error);
    });
});

const circle = document.querySelector(".circle");
const betForm = document.getElementById("betForm");
const betTypeInput = document.getElementById("bet_type");
const betNumberInput = document.getElementById("bet_number");
const resultDiv = document.getElementById("result");
const betButtons = document.querySelectorAll(".button");
const exactNumberOptions = document.querySelector(".exact-number-options");
const numbersGrid = document.querySelector(".numbers-grid");
const submitButton = document.querySelector(".bet-btn");
const payout = document.getElementById("payout");

let selectedBetType = null;
let selectedExactNumber = null;

const userIdInput = document.getElementById("user_id");
const userId = userIdInput ? userIdInput.value : null;

// generate nubmer buttons
const numbers = ["00", ...Array.from({ length: 37 }, (_, i) => i.toString())];
numbers.forEach((number) => {
  const btn = document.createElement("button");
  btn.classList.add("number-btn");
  btn.textContent = number;
  btn.dataset.number = number;
  numbersGrid.appendChild(btn);

  btn.addEventListener("click", () => {
    document
      .querySelectorAll(".number-btn")
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    selectedExactNumber = number;
    betNumberInput.value = number;
  });
});

// add event listener for every button is clicked
betButtons.forEach((button) => {
  button.addEventListener("click", () => {
    betButtons.forEach((btn) => btn.classList.remove("active"));
    button.classList.add("active");

    selectedBetType = button.getAttribute("data-bet");
    betTypeInput.value = selectedBetType;

    if (selectedBetType === "exact") {
      exactNumberOptions.style.display = "block";
    } else {
      exactNumberOptions.style.display = "none";
      selectedExactNumber = null;
      betNumberInput.value = selectedBetType;
    }
  });
});

const rouletteNumbers = [
  { number: "0", color: "green" },
  { number: "28", color: "black" },
  { number: "9", color: "red" },
  { number: "26", color: "black" },
  { number: "30", color: "red" },
  { number: "11", color: "black" },
  { number: "7", color: "red" },
  { number: "20", color: "black" },
  { number: "32", color: "red" },
  { number: "17", color: "black" },
  { number: "5", color: "red" },
  { number: "22", color: "black" },
  { number: "34", color: "red" },
  { number: "15", color: "black" },
  { number: "3", color: "red" },
  { number: "24", color: "black" },
  { number: "36", color: "red" },
  { number: "13", color: "black" },
  { number: "1", color: "red" },
  { number: "00", color: "green" },
  { number: "27", color: "red" },
  { number: "10", color: "black" },
  { number: "25", color: "red" },
  { number: "29", color: "black" },
  { number: "12", color: "red" },
  { number: "8", color: "black" },
  { number: "19", color: "red" },
  { number: "31", color: "black" },
  { number: "18", color: "red" },
  { number: "6", color: "black" },
  { number: "21", color: "red" },
  { number: "33", color: "black" },
  { number: "16", color: "red" },
  { number: "4", color: "black" },
  { number: "23", color: "red" },
  { number: "35", color: "black" },
  { number: "14", color: "red" },
  { number: "2", color: "black" },
];

// add number to roulette
rouletteNumbers.forEach((slot, index) => {
  const angle = (360 / rouletteNumbers.length) * index;
  const el = document.createElement("div");
  el.classList.add("number");

  el.style.transform = `rotate(${angle}deg) translate(130px) rotate(-${angle}deg) translate(-50%, -50%)`;
  el.style.top = "50%";
  el.style.left = "50%";
  el.textContent = slot.number;
  circle.appendChild(el);
});

betForm.addEventListener("submit", function (e) {
  e.preventDefault();
  const betAmount = document.getElementById("bet_amount").value;
  const betType = document.getElementById("bet_type").value;
  const betNumber = document.getElementById("bet_number").value;

  if (!betType) {
    alert("Please select a bet option!");
    return;
  }

  if (betType === "exact" && !selectedExactNumber) {
    alert("Please select an exact number!");
    return;
  }
  
  submitButton.disabled = true;

  // generate rotation angle 
  const rotation =
    360 * Math.floor(Math.random() * 10) + Math.floor(Math.random() * 360);
  circle.style.transform = `rotate(${rotation - 90}deg)`; // minus 90 degrees for the offset
  circle.classList.add("spinning");

  // after spinning check if the player win
  setTimeout(() => {
    submitButton.disabled = false; // Re-enable the bet button after spinning
    circle.classList.remove("spinning");
    const actualRotation = (rotation - 5) % 360;
    const index = Math.floor((actualRotation / 360) * rouletteNumbers.length);
    const winningNumber =
      rouletteNumbers[
        (rouletteNumbers.length - index - 1) % rouletteNumbers.length
      ].number;
    let payoutResult;
    if (betType === "red" || betType === "black") {
      const winningColor = rouletteNumbers.find(
        (num) => num.number === winningNumber
      )?.color;
      if (betType === winningColor) {
        resultDiv.textContent = `You won! The color was ${winningColor}.`;
        payoutResult = betAmount * 2;
      } else {
        resultDiv.textContent = `You lost. The color was ${winningColor}.`;
        payoutResult = betAmount * 0;
      }
    } else if (betType === "even" || betType === "odd") {
      if (winningNumber === "0" || winningNumber === "00") {
        resultDiv.textContent = `You lost. The number was ${winningNumber}.`;
      } else {
        const num = parseInt(winningNumber);
        const isEven = num % 2 === 0;
        if ((betType === "even" && isEven) || (betType === "odd" && !isEven)) {
          resultDiv.textContent = `You won! The number was ${winningNumber}, which is ${betType}.`;
          payoutResult = betAmount * 2;
        } else {
          resultDiv.textContent = `You lost. The number was ${winningNumber}, which is ${
            isEven ? "even" : "odd"
          }.`;
          payoutResult = betAmount * 0;
        }
      }
    } else if (betType === "big" || betType === "small") {
      if (winningNumber === "0" || winningNumber === "00") {
        resultDiv.textContent = `You lost. The number was ${winningNumber}.`;
        payoutResult = betAmount * 0;
      } else {
        const num = parseInt(winningNumber);
        if (
          (betType === "big" && num >= 19 && num <= 36) ||
          (betType === "small" && num >= 1 && num <= 18)
        ) {
          resultDiv.textContent = `You won! The number was ${winningNumber}, which is ${betType}.`;
          payoutResult = betAmount * 2;
        } else {
          resultDiv.textContent = `You lost. The number was ${winningNumber}, which is ${
            num >= 19 && num <= 36 ? "big" : "small"
          }.`;
          payoutResult = betAmount * 0;
        }
      }
    } else if (betType === "exact") {
      if (betNumber === winningNumber) {
        payoutResult = betAmount * 35;
        resultDiv.textContent = `You won! The number was ${winningNumber}.`;
      } else {
        payoutResult = 0;
        resultDiv.textContent = `You lost. The number was ${winningNumber}.`;
      }
    }
    payout.textContent = `Payout : ${payoutResult}`;

    fetch("roulette.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `bet_type=${encodeURIComponent(
        betType
      )}&bet_amount=${encodeURIComponent(
        betAmount
      )}&bet_number=${encodeURIComponent(
        betNumber
      )}&winning_number=${encodeURIComponent(winningNumber)}`,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.chips) {
          document.getElementById(
            "user_chips"
          ).innerText = `Chips: ${data.chips}`;
          // Set the max attribute for bet_amount
          const betAmountInput = document.getElementById("bet_amount");
          if (betAmountInput) {
            betAmountInput.max = data.chips;
          }
        }
      })
      .catch((error) => {
        console.error("Error processing the bet:", error);
      });
  }, 4000);
});
