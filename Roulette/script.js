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

let selectedBetType = null;
let selectedExactNumber = null;

const userIdInput = document.getElementById("user_id");
const userId = userIdInput ? userIdInput.value : null;

// 生成具體數字按鈕
const numbers = ["00", ...Array.from({ length: 37 }, (_, i) => i.toString())];
numbers.forEach((number) => {
  const btn = document.createElement("button");
  btn.classList.add("number-btn");
  btn.textContent = number;
  btn.dataset.number = number;
  numbersGrid.appendChild(btn);

  btn.addEventListener("click", () => {
    // 切換選中的數字按鈕樣式
    document
      .querySelectorAll(".number-btn")
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    selectedExactNumber = number;
    betNumberInput.value = number;
  });
});

// 監聽下注按鈕點擊
betButtons.forEach((button) => {
  button.addEventListener("click", () => {
    // 切換選中的下注按鈕樣式
    betButtons.forEach((btn) => btn.classList.remove("active"));
    button.classList.add("active");

    // 設定選中的下注類型
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

// 添加數字到輪盤
rouletteNumbers.forEach((slot, index) => {
  const angle = (360 / rouletteNumbers.length) * index;
  const el = document.createElement("div");
  el.classList.add("number");

  // 調整 transform，使數字底部面向中心
  el.style.transform = `rotate(${angle}deg) translate(130px) rotate(-${angle}deg) translate(-50%, -50%)`;
  el.style.top = "50%";
  el.style.left = "50%";
  el.textContent = slot.number;
  circle.appendChild(el);
});

betForm.addEventListener("submit", function (e) {
  e.preventDefault();
  submitButton.disabled = true;
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

  if (userId === null) {
    alert("Please log in first.");
    return;
  }

  // 生成隨機旋轉角度
  const rotation =
    360 * Math.floor(Math.random() * 10) + Math.floor(Math.random() * 360);
  circle.style.transform = `rotate(${rotation - 90}deg)`; // 減去90度以配合初始旋轉
  circle.classList.add("spinning");

  // 在旋轉結束後確定贏家
  setTimeout(() => {
    submitButton.disabled = false; // Re-enable the bet button after spinning
    circle.classList.remove("spinning");
    const actualRotation = (rotation - 5) % 360;
    const index = Math.floor((actualRotation / 360) * rouletteNumbers.length);
    const winningNumber =
      rouletteNumbers[
        (rouletteNumbers.length - index - 1) % rouletteNumbers.length
      ].number;

    if (betType === "red" || betType === "black") {
      const winningColor = rouletteNumbers.find(
        (num) => num.number === winningNumber
      )?.color;
      if (betType === winningColor) {
        resultDiv.textContent = `You won! The color was ${winningColor}.`;
      } else {
        resultDiv.textContent = `You lost. The color was ${winningColor}.`;
      }
    } else if (betType === "even" || betType === "odd") {
      if (winningNumber === "0" || winningNumber === "00") {
        resultDiv.textContent = `You lost. The number was ${winningNumber}.`;
      } else {
        const num = parseInt(winningNumber);
        const isEven = num % 2 === 0;
        if ((betType === "even" && isEven) || (betType === "odd" && !isEven)) {
          resultDiv.textContent = `You won! The number was ${winningNumber}, which is ${betType}.`;
        } else {
          resultDiv.textContent = `You lost. The number was ${winningNumber}, which is ${
            isEven ? "even" : "odd"
          }.`;
        }
      }
    } else if (betType === "big" || betType === "small") {
      if (winningNumber === "0" || winningNumber === "00") {
        resultDiv.textContent = `You lost. The number was ${winningNumber}.`;
      } else {
        const num = parseInt(winningNumber);
        if (
          (betType === "big" && num >= 19 && num <= 36) ||
          (betType === "small" && num >= 1 && num <= 18)
        ) {
          resultDiv.textContent = `You won! The number was ${winningNumber}, which is ${betType}.`;
        } else {
          resultDiv.textContent = `You lost. The number was ${winningNumber}, which is ${
            num >= 19 && num <= 36 ? "big" : "small"
          }.`;
        }
      }
    } else if (betType === "exact") {
      if (betNumber === winningNumber) {
        resultDiv.textContent = `You won! The number was ${winningNumber}.`;
      } else {
        resultDiv.textContent = `You lost. The number was ${winningNumber}.`;
      }
    }

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
    // 這裡可以添加與後端交互的代碼，例如通過 AJAX 發送下注結果
  }, 4000);
});
