var Vowel = new Array(123),
    VowelCombination = new Array(6),
    Consonant = new Array(123),
    ConsonantCombination = new Array(27),
    SymbolCode = new Array(60),
    VIRAM = 1,
    VRU = 2,
    RU = 3,
    ZWJ = 4,
    ZWNJ = 5,
    language = "Hindi";
SymbolCode[32] = " ", SymbolCode[58] = "à¤ƒ", SymbolCode[48] = "à¥¦", SymbolCode[49] = "à¥§", SymbolCode[50] = "à¥¨", SymbolCode[51] = "à¥©", SymbolCode[52] = "à¥ª", SymbolCode[53] = "à¥«", SymbolCode[54] = "à¥¬", SymbolCode[55] = "à¥­", SymbolCode[56] = "à¥®", SymbolCode[57] = "à¥¯", Vowel[97] = "à¤…", Vowel[65] = "à¤†", Vowel[105] = "à¤‡", Vowel[73] = "à¤ˆ", Vowel[117] = "à¤‰", Vowel[85] = "à¤Š", Vowel[82] = "à¤‹", Vowel[69] = "à¤", Vowel[101] = "à¤", Vowel[79] = "à¤‘", Vowel[111] = "à¤“", VowelCombination[0] = new Array(3), VowelCombination[0][0] = 97, VowelCombination[0][1] = 97, VowelCombination[0][2] = "à¤†", VowelCombination[1] = new Array(3), VowelCombination[1][0] = 101, VowelCombination[1][1] = 101, VowelCombination[1][2] = "à¤ˆ", VowelCombination[2] = new Array(3), VowelCombination[2][0] = 111, VowelCombination[2][1] = 111, VowelCombination[2][2] = "à¤Š", VowelCombination[3] = new Array(3), VowelCombination[3][0] = 82, VowelCombination[3][1] = 85, VowelCombination[3][2] = "à¤‹", VowelCombination[4] = new Array(3), VowelCombination[4][0] = 97, VowelCombination[4][1] = 105, VowelCombination[4][2] = "à¤", VowelCombination[5] = new Array(3), VowelCombination[5][0] = 97, VowelCombination[5][1] = 117, VowelCombination[5][2] = "à¤”", Consonant[94] = "à¤", Consonant[77] = "à¤‚", Consonant[107] = "à¤•", Consonant[103] = "à¤—", Consonant[106] = "à¤œ", Consonant[122] = "à¤", Consonant[84] = "à¤Ÿ", Consonant[68] = "à¤¡", Consonant[78] = "à¤£", Consonant[116] = "à¤¤", Consonant[100] = "à¤¦", Consonant[110] = "à¤¨", Consonant[112] = "à¤ª", Consonant[102] = "à¤«", Consonant[98] = "à¤¬", Consonant[109] = "à¤®", Consonant[121] = "à¤¯", Consonant[114] = "à¤°", Consonant[108] = "à¤²", Consonant[76] = "à¤³", Consonant[118] = "à¤µ", Consonant[119] = "à¤µ", Consonant[115] = "à¤¸", Consonant[120] = "à¤•à¥à¤·", Consonant[104] = "à¤¹", Consonant[97] = "", Consonant[VIRAM] = "à¥", Consonant[ZWJ] = "â€", Consonant[ZWNJ] = "â€Œ", Consonant[65] = "à¤¾", Consonant[105] = "à¤¿", Consonant[73] = "à¥€", Consonant[117] = "à¥", Consonant[85] = "à¥‚", Consonant[VRU] = "à¥ƒ", Consonant[69] = "à¥…", Consonant[101] = "à¥‡", Consonant[79] = "à¥‰", Consonant[111] = "à¥‹", Consonant[75] = "à¥˜", Consonant[71] = "à¥š", Consonant[90] = "à¥›", Consonant[70] = "à¥ž", Consonant[89] = "à¥Ÿ", ConsonantCombination[0] = new Array(3), ConsonantCombination[0][0] = 107, ConsonantCombination[0][1] = 104, ConsonantCombination[0][2] = "à¤–", ConsonantCombination[1] = new Array(3), ConsonantCombination[1][0] = 103, ConsonantCombination[1][1] = 104, ConsonantCombination[1][2] = "à¤˜", ConsonantCombination[2] = new Array(3), ConsonantCombination[2][0] = 99, ConsonantCombination[2][1] = 104, ConsonantCombination[2][2] = "à¤š", ConsonantCombination[3] = new Array(3), ConsonantCombination[3][0] = 67, ConsonantCombination[3][1] = 104, ConsonantCombination[3][2] = "à¤›", ConsonantCombination[4] = new Array(3), ConsonantCombination[4][0] = 84, ConsonantCombination[4][1] = 104, ConsonantCombination[4][2] = "à¤ ", ConsonantCombination[5] = new Array(3), ConsonantCombination[5][0] = 68, ConsonantCombination[5][1] = 104, ConsonantCombination[5][2] = "à¤¢", ConsonantCombination[6] = new Array(3), ConsonantCombination[6][0] = 116, ConsonantCombination[6][1] = 104, ConsonantCombination[6][2] = "à¤¥", ConsonantCombination[7] = new Array(3), ConsonantCombination[7][0] = 100, ConsonantCombination[7][1] = 104, ConsonantCombination[7][2] = "à¤§", ConsonantCombination[8] = new Array(3), ConsonantCombination[8][0] = 112, ConsonantCombination[8][1] = 104, ConsonantCombination[8][2] = "à¤«", ConsonantCombination[9] = new Array(3), ConsonantCombination[9][0] = 98, ConsonantCombination[9][1] = 104, ConsonantCombination[9][2] = "à¤­", ConsonantCombination[10] = new Array(3), ConsonantCombination[10][0] = 115, ConsonantCombination[10][1] = 104, ConsonantCombination[10][2] = "à¤¶", ConsonantCombination[11] = new Array(3), ConsonantCombination[11][0] = 83, ConsonantCombination[11][1] = 104, ConsonantCombination[11][2] = "à¤·", ConsonantCombination[12] = new Array(3), ConsonantCombination[12][0] = 74, ConsonantCombination[12][1] = 104, ConsonantCombination[12][2] = "à¤œà¥à¤ž", ConsonantCombination[13] = new Array(3), ConsonantCombination[13][0] = 97, ConsonantCombination[13][1] = 97, ConsonantCombination[13][2] = "à¤¾", ConsonantCombination[14] = new Array(3), ConsonantCombination[14][0] = 101, ConsonantCombination[14][1] = 101, ConsonantCombination[14][2] = "à¥€", ConsonantCombination[15] = new Array(3), ConsonantCombination[15][0] = 111, ConsonantCombination[15][1] = 111, ConsonantCombination[15][2] = "à¥‚", ConsonantCombination[16] = new Array(3), ConsonantCombination[16][0] = 97, ConsonantCombination[16][1] = 105, ConsonantCombination[16][2] = "à¥ˆ", ConsonantCombination[17] = new Array(3), ConsonantCombination[17][0] = 97, ConsonantCombination[17][1] = 117, ConsonantCombination[17][2] = "à¥Œ", ConsonantCombination[18] = new Array(3), ConsonantCombination[18][0] = 78, ConsonantCombination[18][1] = 71, ConsonantCombination[18][2] = "à¤™", ConsonantCombination[19] = new Array(3), ConsonantCombination[19][0] = 78, ConsonantCombination[19][1] = 89, ConsonantCombination[19][2] = "à¤ž", ConsonantCombination[20] = new Array(3), ConsonantCombination[20][0] = 75, ConsonantCombination[20][1] = 104, ConsonantCombination[20][2] = "à¥™", ConsonantCombination[21] = new Array(3), ConsonantCombination[21][0] = 68, ConsonantCombination[21][1] = 68, ConsonantCombination[21][2] = "à¥œ", ConsonantCombination[22] = new Array(3), ConsonantCombination[22][0] = 68, ConsonantCombination[22][1] = 72, ConsonantCombination[22][2] = "à¥", ConsonantCombination[23] = new Array(3), ConsonantCombination[23][0] = 78, ConsonantCombination[23][1] = 78, ConsonantCombination[23][2] = "à¤©", ConsonantCombination[24] = new Array(3), ConsonantCombination[24][0] = 82, ConsonantCombination[24][1] = 82, ConsonantCombination[24][2] = "à¤±", ConsonantCombination[25] = new Array(3), ConsonantCombination[25][0] = 76, ConsonantCombination[25][1] = 76, ConsonantCombination[25][2] = "à¤´", ConsonantCombination[26] = new Array(3), ConsonantCombination[26][0] = 106, ConsonantCombination[26][1] = 104, ConsonantCombination[26][2] = "à¤";
var prevkey = 32,
    ppkey = 32,
    hidden = !1,
    posChanged = !0,
    isEng = !1,
    previousConsonant = !1,
    previouspreviousConsonant = !1,
    pVowel = !1,
    pppC = !1,
    isHinLetter = !1;

function ClearAllParameters() {
    prevkey = 32, hidden = !1, posChanged = !0, previousConsonant = !1, previouspreviousConsonant = !1, pppC = !1, pVowel = !1, isHinLetter = !1
}

function changeCursor(n) {
    n.createTextRange && (n.cursorPos = document.selection.createRange().duplicate())
}

function GetVowelCombination(n, o) {
    var e = 0;
    for (e = 0; e < VowelCombination.length; e++)
        if (VowelCombination[e][0] == n && VowelCombination[e][1] == o) return VowelCombination[e][2]
}

function GetConsonantCombination(n, o) {
    var e = 0;
    for (e = 0; e < ConsonantCombination.length; e++)
        if (ConsonantCombination[e][0] == n && ConsonantCombination[e][1] == o) return ConsonantCombination[e][2]
}

function isLetter(n) {
    return n >= "a" && n <= "z" || n >= "A" && n <= "Z"
}

function isDigit(n) {
    return n >= "0" && n <= "9"
}
var activeWord = "",
    txtFieldTTHide = !1;

function hideTxtBoxTT() {
    txtFieldTTHide || (hideTooltip("txtFieldInput"), txtFieldTTHide = !0)
}

function displayRelatedWords(n, o) {
    switch (n) {
        case "a":
        case "o":
        case "i":
        case "I":
        case "e":
        case "E":
        case "A":
        case "O":
        case "u":
        case "U":
            activeWord = "";
            break;
        case "k":
        case "g":
        case "c":
        case "C":
        case "j":
        case "J":
        case "z":
        case "T":
        case "N":
        case "t":
        case "d":
        case "n":
        case "p":
        case "f":
        case "b":
        case "m":
        case "y":
        case "l":
        case "v":
        case "s":
        case "w":
        case "x":
            activeWord = n;
            break;
        case "h":
        case "G":
        case "Y":
        case "K":
        case "G":
        case "Z":
        case "H":
        case "F":
        case "S":
        case "r":
        case "D":
        case "L":
            activeWord += n
    }
    displayInDiv(activeWord, o)
}

function displayInDiv(n, o) {
    var e = document.getElementById(o);
    if (null != e) switch (n) {
        case "k":
            displayCombination(e, "k", "&#x0915;");
            break;
        case "kh":
            displayCombination(e, "kh", "&#x0916;");
            break;
        case "g":
            displayCombination(e, "g", "&#x0917;");
            break;
        case "gh":
            displayCombination(e, n, "&#x0918;");
            break;
        case "c":
            displayAdditional(e, "ch", "cha", "Cha", "&#x091A;", "&#x091A;", "&#x091B;");
            break;
        case "ch":
            displayCombination(e, "ch", "&#x091A;");
            break;
        case "Ch":
            displayCombination(e, "Ch", "&#x091B;");
            break;
        case "NG":
            displayCombination(e, n, "&#x0919;");
            break;
        case "j":
            displayAdditional(e, n, "za", "Za", "&#x091C;", "&#x091D;", "&#x095B;");
            break;
        case "z":
            displayCombination(e, n, "&#x091D;");
            break;
        case "NY":
            displayCombination(e, n, "&#x091E;");
            break;
        case "T":
            displayCombination(e, n, "&#x091F;");
            break;
        case "Th":
            displayCombination(e, n, "&#x0920;");
            break;
        case "D":
            displayCombination(e, n, "&#x0921;");
            break;
        case "Dh":
            displayCombination(e, n, "&#x0922;");
            break;
        case "N":
            displayCombination(e, n, "&#x0923;");
            break;
        case "t":
            displayAdditional(e, n, "Ta", "Tha", "&#x0924;", "&#x091F;", "&#x0920;");
            break;
        case "th":
            displayCombination(e, n, "&#x0925;");
            break;
        case "d":
            displayAdditional(e, n, "Da", "Dha", "&#x0926;", "&#x0921;", "&#x0922;");
            break;
        case "dh":
            displayCombination(e, n, "&#x0927;");
            break;
        case "n":
            displayAdditional(e, n, "Na", "NNa", "&#x0928;", "&#x0923;", "&#x0929;");
            break;
        case "p":
            displayCombination(e, n, "&#x092A;");
            break;
        case "f":
            displayCombination(e, n, "&#x092B;");
            break;
        case "b":
            displayCombination(e, n, "&#x092C;");
            break;
        case "bh":
            displayCombination(e, n, "&#x092D;");
            break;
        case "m":
            displayCombination(e, n, "&#x092E;");
            break;
        case "y":
            displayCombination(e, n, "&#x092F;");
            break;
        case "r":
            displayCombination(e, n, "&#x0930;");
            break;
        case "l":
            displayCombination(e, n, "&#x0932;");
            break;
        case "v":
            displayCombination(e, n, "&#x0935;");
        case "w":
            displayCombination(e, n, "&#x0935;");
            break;
        case "s":
            displayAdditional(e, n, "sha", "Sha", "&#x0938;", "&#x0936;", "&#x0937;");
            break;
        case "sh":
            displayCombination(e, n, "&#x0936;");
            break;
        case "Sh":
            displayCombination(e, n, "&#x0937;");
            break;
        case "h":
            displayCombination(e, n, "&#x0939;");
            break;
        case "L":
            displayCombination(e, n, "&#x0933;");
            break;
        case "kSh":
        case "x":
            displayCombination(e, n, "&#x0915;&#x094D;&#x0937;");
            break;
        case "Jh":
            displayCombination(e, n, "&#x091C;&#x094D;&#x091E;");
            break;
        case "tr":
            displayCombination(e, n, "&#x0924;&#x094D;&#x0930;");
            break;
        case "LL":
            displayCombination(e, n, "&#x0934;");
            break;
        case "K":
            displayCombination(e, n, "&#x0958;");
            break;
        case "Kh":
            displayCombination(e, n, "&#x0959;");
            break;
        case "G":
            displayCombination(e, n, "&#x095A;");
            break;
        case "Z":
            displayCombination(e, n, "&#x095B;");
            break;
        case "DD":
            displayCombination(e, n, "&#x095C;");
            break;
        case "DH":
            displayCombination(e, n, "&#x095D;");
            break;
        case "F":
            displayCombination(e, n, "&#x095E;");
            break;
        case "Y":
            displayCombination(e, n, "&#x095F;")
    }
}

function Get2DArray(n, o) {
    for (var e = new Array(n), a = 0; a < e.length; a++) e[a] = new Array(o);
    return e
}

function displayAdditional(n, o, e, a, t, i, s) {
    var r = Get2DArray(17, 2),
        C = GetNormalDisplayArray(o, t);
    r[0][0] = e, r[0][1] = i, r[1][0] = a, r[1][1] = s;
    for (var p = 0; p < C.length; p++) r[p + 2][0] = C[p][0], r[p + 2][1] = C[p][1];
    displayArrayInTable(n, r)
}

function GetNormalDisplayArray(n, o) {
    var e = Get2DArray(15, 2);
    return e[0][0] = n, e[0][1] = o + "&#x094D;", e[1][0] = n + "a", e[1][1] = o, e[2][0] = n + "A", e[2][1] = o + "&#x093E;", e[3][0] = n + "e", e[3][1] = o + "&#x0947;", e[4][0] = n + "ai", e[4][1] = o + "&#x0948;", e[5][0] = n + "i", e[5][1] = o + "&#x093F;", e[6][0] = n + "I", e[6][1] = o + "&#x0940;", e[7][0] = n + "o", e[7][1] = o + "&#x094B;", e[8][0] = n + "au", e[8][1] = o + "&#x094C;", e[9][0] = n + "u", e[9][1] = o + "&#x0941;", e[10][0] = n + "U", e[10][1] = o + "&#x0942;", e[11][0] = n + "M", e[11][1] = o + "&#x0902;", e[12][0] = n + "~M", e[12][1] = o + "&#x0901;", e[13][0] = n + "^", e[13][1] = o + "&#x094D;&#x200D;", e[14][0] = n + "^^", e[14][1] = o + "&#x094D;&#x200C;", e
}

function displayCombination(n, o, e) {
    displayArrayInTable(n, GetNormalDisplayArray(o, e))
}

function displaykh(n) {
    n.innerHTML += "kh|kha|khi|khI|khe"
}

function displayArrayInTable(n, o) {
    if (null != n) {
        null != n.rows && null != n.rows.length && n.rows.length >= 2 && (n.deleteRow(1), n.deleteRow(0));
        var e = n.insertRow(0),
            a = e.insertCell(0);
        a.innerHTML = "<span>" + o[0][1] + "</span>";
        for (var t = 1; t < o.length; t++)(a = e.insertCell(t)).innerHTML = "<span class='awSpan2'>" + o[t][1] + "</span>";
        (a = (e = n.insertRow(1)).insertCell(0)).innerHTML = "<span>" + o[0][0] + "</span>";
        for (t = 1; t < o.length; t++)(a = e.insertCell(t)).innerHTML = "<span class='awSpan2'>" + o[t][0] + "</span>"
    }
}

function displayArray(n, o) {
    var e = "";
    2 == o[0][0].length && (e = " "), n.innerHTML += "<span>" + o[0][1] + "</span>";
    for (var a = 1; a < o.length; a++) n.innerHTML += "<span class='awSpan2'>" + o[a][1] + e + "</span>";
    n.innerHTML += "<br>", n.innerHTML += "<span>" + o[0][0] + "</span>";
    for (a = 1; a < o.length; a++) n.innerHTML += "<span class='awSpan2'>" + o[a][0] + "</span>";
    n.innerHTML += "<br>"
}

function GetKeyChar(n) {
    var o = window.event ? event : n;
    if (o.altKey || o.ctrlKey) return !0;
    var e = 0;
    return null == (e = document.all ? o.keyCode : o.which) && (e = n.keyCode ? n.keyCode : n.which), String.fromCharCode(e)
}

function HindiKeyBoard() {}
var inputBoxIds = new Array,
    inputBoxCount = 0;

function GetNewKeyboard(n) {
    GetNewKeyboard(n, "awTable")
}

function GetNewKeyboard(n, o) {
    var e = new HindiKeyBoard;
    return e.prevkey = 32, e.ppkey = 32, e.hidden = !1, e.posChanged = !0, e.isEng = !1, e.previousConsonant = !1, e.previouspreviousConsonant = !1, e.pVowel = !1, e.pppC = !1, e.isHinLetter = !1, e.elemID = n, e.awTableId = o, e
}

function ClearAllParameters(n) {
    n.prevkey = 32, n.hidden = !1, n.posChanged = !0, n.previousConsonant = !1, n.previouspreviousConsonant = !1, n.pppC = !1, n.pVowel = !1, n.isHinLetter = !1
}

function positionChange(n, o) {
    var e = document.layers ? n.which : document.all ? event.keyCode : document.getElementById ? n.keyCode : 0;
    if (null == e && (e = n.keyCode ? n.keyCode : n.which), 123 == e && (o.isEng = !o.isEng, ClearAllParameters(o)), e >= 37 && e <= 40 && (o.posChanged = !0), 8 == e) {
        o.previousConsonant = o.previouspreviousConsonant, o.previouspreviousConsonant = o.pppC, o.pppC = !1, o.isHinLetter && ClearAllParameters(o);
        var a = document.getElementById(o.elemID);
        null != a.value && "" != a.value && 1 != a.value.length || ClearAllParameters(o)
    }
}

function change(n, o) {
    var e = document.getElementById(o.elemID),
        a = 0,
        t = "",
        i = window.event ? event : n;
    if (i.altKey || i.ctrlKey) return !0;
    var s, r = 0,
        C = 0,
        p = 0;
    if (null == (r = document.all ? i.keyCode : i.which) && (r = n.keyCode ? n.keyCode : n.which), r < 32 || r >= 33 && r <= 47 || r >= 59 && r <= 64 || r >= 91 && r <= 96 && 94 != r || r >= 123 && r <= 127 && 126 != r || r > 255) return ClearAllParameters(o), !0;
    switch (s = String.fromCharCode(r), o.posChanged && (o.prevkey = 32, o.hidden = !1, p = 0, o.previousConsonant = !1, o.previouspreviousConsonant = !1, o.pppC = !1), displayRelatedWords(s, o.awTableId), s) {
        case "a":
            var l = GetConsonantCombination(o.prevkey, r),
                b = GetVowelCombination(o.prevkey, r);
            o.pVowel = !0, o.previousConsonant ? (a = 0, t = Consonant[r], o.isHinLetter = !0) : void 0 !== b ? (o.previouspreviousConsonant ? (a = 0, t = l) : (a--, t = b), o.isHinLetter = !1) : (a = 0, t = Vowel[r], o.isHinLetter = !1), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1;
            break;
        case "e":
        case "i":
        case "o":
        case "u":
        case "A":
        case "I":
        case "U":
        case "O":
        case "E":
            l = GetConsonantCombination(o.prevkey, r), b = GetVowelCombination(o.prevkey, r);
            o.pVowel = !0, o.isHinLetter = !1, o.previousConsonant ? (a = 0, t = Consonant[r]) : void 0 !== b ? o.previouspreviousConsonant ? (o.prevkey != r ? a = 0 : a -= 1, t = l) : (a--, t = b) : (a = 0, t = Vowel[r]), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1;
            break;
        case "R":
            o.previousConsonant ? (a = 0, t = Consonant[VRU]) : (a = 0, t = Vowel[r]), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1, o.prevkey = r, o.pVowel = !0, o.isHinLetter = !1;
            break;
        case "~":
            a = 0, t = "~", o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1, prevchar = s, o.pVowel = !0, o.isHinLetter = !1;
            break;
        case "^":
            126 == o.prevkey ? (a -= 1, t = "^") : o.previousConsonant ? t = Consonant[VIRAM] + Consonant[ZWJ] : o.prevkey == r ? (a -= 2, t = Consonant[VIRAM] + Consonant[ZWNJ]) : t = "^", o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1, prevchar = s, o.isHinLetter = !1;
            break;
        case "M":
            126 != o.prevkey ? (a = 0, t = Consonant[r]) : (a -= 1, t = Consonant[94]), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1, prevchar = o.keychar, o.pVowel = !0, o.isHinLetter = !1;
            break;
        case " ":
            if (o.pVowel = !1, 32 == o.prevkey) return;
            a = 0, t = SymbolCode[r], o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1, o.hidden = !1, o.ppkey = o.prevkey, o.prevkey = r, o.isHinLetter = !1;
            break;
        default:
            l = GetConsonantCombination(o.prevkey, r);
            o.pVowel = !1, o.isHinLetter = !1, o.hidden ? void 0 !== l ? (a = 0, t = o.previouspreviousConsonant ? Consonant[VIRAM] + l : l, ClearAllParameters(o), o.previouspreviousConsonant = !1, o.pppC = !1, o.previousConsonant = !0) : void 0 !== Consonant[r] ? (a = 0, t = o.previousConsonant && !o.previouspreviousConsonant ? Consonant[VIRAM] + Consonant[r] : Consonant[r], ClearAllParameters(o), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !0, o.hidden = !1) : (a = 0, o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1) : void 0 !== l ? (a -= 1, t = l, ClearAllParameters(o), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = !1, o.previousConsonant = !0) : void 0 !== Consonant[r] ? (a = 0, t = o.previousConsonant && !o.previouspreviousConsonant ? Consonant[VIRAM] + Consonant[r] : Consonant[r], ClearAllParameters(o), o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !0) : void 0 !== SymbolCode[r] ? (a = 0, t = SymbolCode[r], o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !1) : (o.pppC = o.previouspreviousConsonant, o.previouspreviousConsonant = o.previousConsonant, o.previousConsonant = !0, o.hidden = !0)
    }
    if (o.ppkey = o.prevkey, o.prevkey = r, e.setSelectionRange) {
        C = e.selectionStart;
        var u = e.value.substring(0, C + a),
            d = e.value.substring(C, e.value.length),
            m = e.scrollTop;
        p = void 0 !== t ? C + a + t.length : C + a, e.value = u + t + d, e.scrollTop = m, e.focus(), e.setSelectionRange(p, p)
    } else if (e.createTextRange && e.cursorPos) {
        var c = e.cursorPos;
        c.moveStart("character", a), c.text = " " == c.text.charAt(c.text.length - 1) ? t + " " : t, c.collapse(!1), c.scrollIntoView(!0)
    } else 0 == a ? e.value += t : e.value = e.value.substring(0, e.value.length - 1) + t;
    return e.focus(), o.posChanged = !1, !1
}

function processInput(n, o) {
    return change(n, o)
}

function TextBoxInput() {}

function TextAreaInput() {}

function CreateHindiGTextBox(n) {
    hinTextBoxObj = new TextBoxInput, hinTextBoxObj.Id = n, hinTextBoxObj.Name = n, hinTextBoxObj.Value = "", hinTextBoxObj.Size = 60, hinTextBoxObj.Help = !1, hinTextBoxObj.UseHinkhoj = !1, hinTextBoxObj.Logo = !1, CreateHindiTextBoxSelectedParam(hinTextBoxObj)
}

function CreateCustomHindiGTextBox(n, o, e) {
    hinTextBoxObj = new TextBoxInput, hinTextBoxObj.Id = n, hinTextBoxObj.Name = n, hinTextBoxObj.Value = o, hinTextBoxObj.Size = e, hinTextBoxObj.Help = !1, hinTextBoxObj.UseHinkhoj = !1, hinTextBoxObj.Logo = !1, CreateHindiTextBoxSelectedParam(hinTextBoxObj)
}

function CreateHindiTextBox(n) {
    CreateCustomHindiTextBox(n, "", 60, !1)
}

function CreateCustomHindiTextBox(n, o, e, a) {
    hinTextBoxObj = new TextBoxInput, hinTextBoxObj.Id = n, hinTextBoxObj.Name = n, hinTextBoxObj.Value = o, hinTextBoxObj.Size = e, hinTextBoxObj.Help = a, hinTextBoxObj.Logo = !!a, hinTextBoxObj.UseHinkhoj = !0, CreateHindiTextBoxSelectedParam(hinTextBoxObj)
}

function CreateHindiTextBoxSelectedParam(n) {
    if (null != n.Id) {
        var o = n.Id + "_awTable",
            e = n.Id + "_cbId";
        n.UseHinkhoj && (inputBoxIds[inputBoxCount] = GetNewKeyboard(n.Id, o)), document.write("<input type='text'"), null != n.Value && document.write(" value='" + n.Value + "'"), null != n.Size && document.write(" size='" + n.Size + "'"), null != n.Class && document.write(" Class='" + n.Class + "'"), null != n.Name && document.write(" Name='" + n.Name + "'"), document.write(" id='" + n.Id + "'"), n.UseHinkhoj ? document.write(" onkeypress='if (!inputBoxIds[" + inputBoxCount + "].isEng){ return processInput(event,inputBoxIds[" + inputBoxCount + "]);} else {return true;}' onKeyUp='changeCursor(this);' onkeydown='positionChange(event,inputBoxIds[" + inputBoxCount + "]);' onFocus='changeCursor(this);' onClick='changeCursor(this);'") : document.write(" onmouseout=ChangeHindi('" + n.Id + "') "), document.write(">"), n.UseHinkhoj || document.write('<input type="checkbox" id="' + e + '" onclick="javascript:checkboxClickHandler(\'' + e + '\')" checked>Hindi-[<font color="green">Use <b>ALT-k</b> to change langauge</font>]</input>'), null != n.Logo && n.Logo && document.write("<a style='text-decoration:none' href='http://www.hinkhoj.com/api'><span style='font-size:8pt;color:black'>Powered By:</span><span style='font-size:8pt;color:black;margin-left:5px'><b>www.EasyNepaliTyping.Com</b></span></a>"), null != n.Help && n.Help && document.write("<span class='center-div'><table width='100%' id='" + o + "' border='0' class='awDiv'></table></span>"), n.UseHinkhoj && inputBoxCount++
    } else alert("Error: Id can't be NULL")
}

function CreateHindiTextArea(n) {
    CreateCustomHindiTextArea(n, "", 50, 3, !1)
}

function CreateCustomHindiTextArea(n, o, e, a, t) {
    textAreaInpt = new TextAreaInput, textAreaInpt.Id = n, textAreaInpt.Name = n, textAreaInpt.Value = o, textAreaInpt.Cols = e, textAreaInpt.Rows = a, textAreaInpt.Help = t, textAreaInpt.UseHinkhoj = !0, CreateHindiTextAreaSelectedParam(textAreaInpt)
}

function CreateCustomHindiGTextArea(n, o, e, a) {
    textAreaInpt = new TextAreaInput, textAreaInpt.Id = n, textAreaInpt.Name = n, textAreaInpt.Value = o, textAreaInpt.Cols = e, textAreaInpt.Rows = a, hinTextBoxObj.Help = !1, hinTextBoxObj.UseHinkhoj = !1, hinTextBoxObj.Logo = !1, CreateHindiTextAreaSelectedParam(textAreaInpt)
}

function CreateHindiTextAreaSelectedParam(n) {
    if (null != n.Id) {
        var o = n.Id + "_awTable",
            e = n.Id + "_cbId";
        n.UseHinkhoj && (inputBoxIds[inputBoxCount] = GetNewKeyboard(n.Id, o)), document.write("<textarea class='copiable' "), null != n.Cols && document.write(" cols='" + n.Cols + "'"), null != n.Rows && document.write(" rows='" + n.Rows + "'"), null != n.Class && document.write(" Class='" + n.Class + "'"), null != n.Name && document.write(" Name='" + n.Name + "'"), document.write(" id='" + n.Id + "' "), n.UseHinkhoj && document.write(" onkeypress='if (!inputBoxIds[" + inputBoxCount + "].isEng){ return processInput(event,inputBoxIds[" + inputBoxCount + "]);} else {return true;}' onKeyUp='changeCursor(this);' onkeydown='positionChange(event,inputBoxIds[" + inputBoxCount + "]);' onFocus='changeCursor(this);' onClick='changeCursor(this);'"), document.write(" >"), null != n.Value && document.write(n.Value), document.write("</textarea>"), n.UseHinkhoj || document.write('<input type="checkbox" id="' + e + '" onclick="javascript:checkboxClickHandler(\'' + e + '\')" checked>Hindi-[<font color="green">Use <b>ALT-k</b> to change langauge</font>]</input>'), null != n.Help && n.Help && document.write("<span class='center-div'><table width='100%' id='" + o + "' border='0' class='awDiv'></table></span>"), n.UseHinkhoj && inputBoxCount++
    } else alert("Error: Id can't be NULL")
}