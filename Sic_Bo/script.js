let cube1 = document.getElementById('dice1');
let cube2 = document.getElementById('dice2');
let cube3 = document.getElementById('dice3');
let currentClass1 = '';
let currentClass2 = '';
let currentClass3 = '';

function DiceRolling(dice1, dice2, dice3) {
  let showClass1 = 'show-' + dice1;
  let showClass2 = 'show-' + dice2;
  let showClass3 = 'show-' + dice3;

  if ( currentClass1 ) {
    cube1.classList.remove( currentClass1 );
  }
  cube1.classList.add( showClass1 );
  currentClass1 = showClass1;

  if ( currentClass2 ) {
    cube2.classList.remove( currentClass2 );
  }
  cube2.classList.add( showClass2 );
  currentClass2 = showClass2;

  if ( currentClass3 ) {
    cube3.classList.remove( currentClass3 );
  }
  cube3.classList.add( showClass3 );
  currentClass3 = showClass3;
}

//籌碼數量
const bets = {
    Small: 0,
    Big: 0,
    Triple: 0,
    Triple_1: 0,
    Triple_2: 0,
    Triple_3: 0,
    Triple_4: 0,
    Triple_5: 0,
    Triple_6: 0,
    Double_1: 0,
    Double_2: 0,
    Double_3: 0,
    Double_4: 0,
    Double_5: 0,
    Double_6: 0,
    Total4: 0,
    Total5: 0,
    Total6: 0,
    Total7: 0,
    Total8: 0,
    Total9: 0,
    Total10: 0,
    Total11: 0,
    Total12: 0,
    Total13: 0,
    Total14: 0,
    Total15: 0,
    Total16: 0,
    Total17: 0,
    and1_2: 0,
    and1_3: 0,
    and1_4: 0,
    and1_5: 0,
    and1_6: 0,
    and2_3: 0,
    and2_4: 0,
    and2_5: 0,
    and2_6: 0,
    and3_4: 0,
    and3_5: 0,
    and3_6: 0,
    and4_5: 0,
    and4_6: 0,
    and5_6: 0,
    point1: 0,
    point2: 0,
    point3: 0,
    point4: 0,
    point5: 0,
    point6: 0,
  };

  let currentArea = ''; // 目前操作的下注區域
  let basicAmount = 10;
  let currentBet = 0;

  // 開啟彈出框
  function openModal(area) {
    currentArea = area; // 記錄當前區域
    updateModalInfo(area);
    document.getElementById('bet-amount').value = bets[area];
    document.getElementById('modal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }

  // 關閉彈出框
  function closeModal() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }

  function clearBet() {
    document.getElementById('bet-amount').value = 0;
  }

  // 調整籌碼數量
  function adjustBet(amount) {
    const input = document.getElementById('bet-amount');
    let currentValue = parseInt(input.value);
    currentValue = Math.max(0, currentValue + amount * basicAmount); // 不允許小於0
    input.value = currentValue;
  }

  // 保存籌碼數量
  function saveBet() {
    const amount = parseInt(document.getElementById('bet-amount').value);
    currentBet -= bets[currentArea];
    bets[currentArea] = amount;
    currentBet += bets[currentArea];
    document.getElementById('current-bet').innerHTML = currentBet;
    // console.log(currentBet);

    // 更新籌碼顯示
    const chip = document.getElementById(`chip-${currentArea}`);
    const bet = document.getElementById(`bet-${currentArea}`);
    if (amount > 0) {
        chip.style.display = "block";
        bet.style.display = "block";
        bet.innerHTML = "<p>" + amount + "</p>";
    } else {
        chip.style.display = "none";
        bet.style.display = "none";
        bet.innerHTML = 0;
    }

    
    closeModal();
  }

  function rollDice() {
    // 發送 AJAX 請求到後端
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'rollDice',
            bets: JSON.stringify(bets)
        })
    })
    .then(response => response.json())
    .then(data => {
        const { dice, playerChips } = data;
        let original = parseInt(document.getElementById('player-chips').innerText);
        DiceRolling(dice[0], dice[1], dice[2]);
        console.log(dice);
        animateChips(original, playerChips);
        highlightWinningZones(data.winningZones);
    })
    .catch(error => console.error('發生錯誤:', error));
  }

  function highlightWinningZones(zones) {
      zones.forEach(zoneId => {
          const zoneElement = document.getElementById(zoneId);
          if (zoneElement) {
              // 改變邊框顏色
              zoneElement.classList.add('highlight');

              // ?秒後移除
              setTimeout(() => {
                  zoneElement.classList.remove('highlight');
              }, 2500);
          }
      });
  }

  function animateChips(currentValue, targetValue, duration = 1000) {
    const chipsDisplay = document.getElementById("player-chips");
    const startTime = performance.now();

    function animate(time) {
      const elapsed = time - startTime;
      const progress = Math.min(elapsed / duration, 1); // 控制進度在 0-1 間
      const animatedValue = Math.round(
        currentValue + (targetValue - currentValue) * progress
      );

      chipsDisplay.textContent = animatedValue;

      if (progress < 1) {
        requestAnimationFrame(animate); // 繼續動畫
      }
    }

    requestAnimationFrame(animate);
  }

  function QuickBet() {
    let amount = document.getElementById('quick-bet-amount').value;
    // console.log('quickBet');
    const betKeys = Object.keys(bets);
    amount = Math.floor(amount / 10);
    while(amount != 0){
        let randomIndex = Math.floor(Math.random() * betKeys.length);
        currentArea = betKeys[randomIndex];
        let randomAmount = Math.floor(Math.random() * amount) + 1;
        document.getElementById('bet-amount').value = randomAmount * 10 + bets[currentArea];
        saveBet();
        amount -= randomAmount;
    }
    closeQuickBetModal();
  }

  // 開啟彈出框
  function openQuickBetModal() {
    document.getElementById('quick-bet-modal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }

  // 關閉彈出框
  function closeQuickBetModal() {
    document.getElementById('quick-bet-modal').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }

  window.onload = function() {
      // 發送 AJAX 請求到後端
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'onload',
        })
    })
    .then(response => response.json())
    .then(data => {
        const { playerChips } = data;
        document.getElementById('player-chips').innerHTML = `${playerChips}`;
    })
    .catch(error => console.error('發生錯誤:', error));
  }

  function updateModalInfo(area) {
    if(area === 'Small'){
        document.getElementById('whichArea').textContent = 'SMALL';
        document.getElementById('odds').innerHTML = '1 wins 1<br>(lose if any triple appears)';
    }
    else if(area === 'Big'){
        document.getElementById('whichArea').textContent = 'BIG';
        document.getElementById('odds').innerHTML = '1 wins 1<br>(lose if any triple appears)';
    }
    else if(area === 'Triple'){
        document.getElementById('whichArea').textContent = 'Any Triple';
        document.getElementById('odds').textContent = '1 wins 30';
    }
    else if(area.startsWith("Triple_")){
        document.getElementById('whichArea').textContent = 'Triple ' + area[area.length-1];
        document.getElementById('odds').textContent = '1 wins 180';
    }
    else if(area.startsWith("Double_")){
        document.getElementById('whichArea').textContent = 'Double ' + area[area.length-1];
        document.getElementById('odds').textContent = '1 wins 10';
    }
    else if(area.startsWith("Total")){
        document.getElementById('whichArea').textContent = area.substr(5, 2) + ' in total';
        if(area.substr(5, 2) === '4' || area.substr(5, 2) === '17'){
            document.getElementById('odds').textContent = '1 wins 60';
        }
        else if(area.substr(5, 2) === '5' || area.substr(5, 2) === '16'){
            document.getElementById('odds').textContent = '1 wins 30';
        }
        else if(area.substr(5, 2) === '6' || area.substr(5, 2) === '15'){
            document.getElementById('odds').textContent = '1 wins 17';
        }
        else if(area.substr(5, 2) === '7' || area.substr(5, 2) === '14'){
            document.getElementById('odds').textContent = '1 wins 12';
        }
        else if(area.substr(5, 2) === '8' || area.substr(5, 2) === '13'){
            document.getElementById('odds').textContent = '1 wins 8';
        }
        else{
            document.getElementById('odds').textContent = '1 wins 6';
        }
    }
    else if(area.startsWith("and")){
        document.getElementById('whichArea').textContent = area[3] + ' and ' + area[5];
        document.getElementById('odds').textContent = '1 wins 5';
    }
    else if(area.startsWith("point")){
        if(area[5] === '1'){
            document.getElementById('whichArea').textContent = 'ONE';
        }
        else if(area[5] === '2'){
            document.getElementById('whichArea').textContent = 'TWO';
        }
        else if(area[5] === '3'){
            document.getElementById('whichArea').textContent = 'THREE';
        }
        else if(area[5] === '4'){
            document.getElementById('whichArea').textContent = 'FOUR';
        }
        else if(area[5] === '5'){
            document.getElementById('whichArea').textContent = 'FIVE';
        }
        else if(area[5] === '6'){
            document.getElementById('whichArea').textContent = 'SIX';
        }
        document.getElementById('odds').innerHTML = '1 wins 1 on one dice<br>1 wins 2 on two dice<br>1 wins 3 on three dice';
    }
  }

// 選擇按鈕
const rollbutton = document.getElementById("roll-button");

// 定義動畫間隔
setInterval(() => {
  rollbutton.classList.add("shaking");

  setTimeout(() => {
    rollbutton.classList.remove("shaking");
  }, 1000); // 對應 CSS 動畫時長
}, 7000); // 每 ? 秒觸發一次
