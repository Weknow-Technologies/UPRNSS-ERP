/**
 * custom_round.js — Shared Rounding Helpers
 * Rule: decimal < 0.50 → floor | = 0.50 → keep | > 0.50 → ceil
 * Include this file in any page that needs rounding.
 */

function customRound(number) {
    number = Number(number);
    if (!isFinite(number) || number === 0) return '0.00';
    var int = Math.floor(number);
    var decimal = number - int;
    if (decimal === 0)    return int.toFixed(2);
    if (decimal <  0.50)  return int.toFixed(2);
    if (decimal === 0.50) return (int + 0.50).toFixed(2);
    return (int + 1).toFixed(2);
}

// Blur event pe input field ko round karo
function roundInputOnBlur(el) {
    var v = parseFloat(el.value);
    if (!isNaN(v)) el.value = customRound(v);
}

// Alias — fund_transfer1.php ke liye
var money = function(v) {
    return customRound(isFinite(Number(v)) ? Number(v) : 0);
};
