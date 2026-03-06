var kruti_array = new Array("ñ","Q+Z","sas","aa",")Z","ZZ","‘","’","“","”","å",  "ƒ",  "„",   "…",   "†",   "‡",   "ˆ",   "‰",   "Š",   "‹", "¶+",   "d+", "[+k","[+", "x+",  "T+",  "t+", "M+", "<+", "Q+", ";+", "j+", "u+","Ùk", "Ù", "ä", "–", "—","é","™","=kk","f=k",  "à",   "á",    "â",   "ã",   "ºz",  "º",   "í", "{k", "{", "=",  "«", "Nî",   "Vî",    "Bî",   "Mî",   "<î", "|", "K", "}","J",   "Vª",   "Mª",  "<ªª",  "Nª",   "Ø",  "Ý", "nzZ",  "æ", "ç", "Á", "xz", "#", ":", "v‚","vks",  "vkS",  "vk",    "v",  "b±", "Ã",  "bZ",  "b",  "m",  "Å",  ",s",  ",",   "_","ô",  "d", "Dk", "D", "[k", "[", "x","Xk", "X", "Ä", "?k", "?",   "³", "pkS",  "p", "Pk", "P",  "N",  "t", "Tk", "T",  ">", "÷", "¥","ê",  "ë",   "V",  "B",   "ì",   "ï", "M+", "<+", "M",  "<", ".k", ".", "r",  "Rk", "R",   "Fk", "F",  ")", "n", "/k", "èk",  "/", "Ë", "è", "u", "Uk", "U",   "i",  "Ik", "I",   "Q",    "¶",  "c", "Ck",  "C",  "Hk",  "H", "e", "Ek",  "E", ";",  "¸",   "j",    "y", "Yk",  "Y",  "G",  "o", "Ok", "O", "'k", "'",   "\"k",  "\"",  "l", "Lk",  "L",   "g",  "È", "z", "Ì", "Í", "Î",  "Ï",  "Ñ",  "Ò",  "Ó",  "Ô",   "Ö",  "Ø",  "Ù","Ük", "Ü","‚",    "ks",   "kS",   "k",  "h",    "q",   "w",   "`",    "s",    "S", "a",    "¡",    "%",     "W",  "•", "·", "∙", "·", "~j",  "~", "\\","+"," ः","^", "*",  "Þ", "ß", "(", "¼", "½", "¿", "À", "¾", "A", "-", "&", "&", "Œ", "]","~ ","@");

var unicode_array = new Array("॰","QZ+","sa","a","र्द्ध","Z","\"","\"","'","'", "०",  "१",  "२",  "३",     "४",   "५",  "६",   "७",   "८",   "९",  "फ़्",  "क़",  "ख़", "ख़्",  "ग़", "ज़्", "ज़",  "ड़",  "ढ़",   "फ़",  "य़",  "ऱ",  "ऩ",  "त्त", "त्त्", "क्त",  "दृ",  "कृ","न्न","न्न्","=k","f=", "ह्न",  "ह्य",  "हृ",  "ह्म",  "ह्र",  "ह्",   "द्द",  "क्ष", "क्ष्", "त्र", "त्र्",  "छ्य",  "ट्य",  "ठ्य",  "ड्य",  "ढ्य", "द्य", "ज्ञ", "द्व", "श्र",  "ट्र",    "ड्र",    "ढ्र",    "छ्र",   "क्र",  "फ्र", "र्द्र",  "द्र",   "प्र", "प्र",  "ग्र", "रु",  "रू", "ऑ",   "ओ",  "औ",  "आ",   "अ", "ईं", "ई",  "ई",   "इ",  "उ",   "ऊ",  "ऐ",  "ए", "ऋ", "क्क", "क", "क", "क्", "ख", "ख्", "ग", "ग", "ग्", "घ", "घ", "घ्", "ङ", "चै",  "च", "च", "च्", "छ", "ज", "ज", "ज्",  "झ",  "झ्", "ञ", "ट्ट",   "ट्ठ",   "ट",   "ठ",   "ड्ड",   "ड्ढ",  "ड़", "ढ़", "ड",   "ढ", "ण", "ण्", "त", "त", "त्", "थ", "थ्",  "द्ध",  "द", "ध", "ध", "ध्", "ध्", "ध्", "न", "न", "न्",    "प", "प", "प्",  "फ", "फ्",  "ब", "ब", "ब्",  "भ", "भ्",  "म",  "म", "म्", "य", "य्",  "र", "ल", "ल", "ल्",  "ळ",  "व", "व", "व्", "श", "श्",  "ष", "ष्", "स", "स", "स्", "ह", "ीं", "्र", "द्द", "ट्ट","ट्ठ","ड्ड","कृ","भ","्य","ड्ढ","झ्","क्र","त्त्","श","श्","ॉ",  "ो",   "ौ",   "ा",   "ी",   "ु",   "ू",   "ृ",   "े",   "ै", "ं",   "ँ",   "ः",   "ॅ",  "ऽ", "ऽ", "ऽ", "ऽ", "्र",  "्", "?", "़",":", "‘",   "’",   "“",   "”",  ";",  "(",    ")",   "{",    "}",   "=", "।", ".", "-",  "µ", "॰", ",","् ","/");
function krutiunicode()
{

var text_size = document.getElementById("project_name_hindi").value.length ;
var kruti_array_length = kruti_array.length ;
var kruti_text = document.getElementById("project_name_hindi").value  ;

    var processed_text = '' ;  

    var n = 0 ;  var o = 0 ;  var r = 1 ;
 
    var max_text_size = 7000;

    while ( r == 1 ) 
    {
     n = o ;

     if ( o < ( text_size - max_text_size ) )  
     { 
      o +=  max_text_size ;
      while (document.getElementById("project_name_hindi").value.charAt ( o ) != ' ') {o--;}
     } 
     else  { o = text_size  ;  r = 0 }

     var kruti_text = document.getElementById("project_name_hindi").value.substring ( n, o )  ;

    replsym( ) ;

    processed_text += kruti_text ;


    document.getElementById("project_name_hindi_unicode").value = processed_text  ;
   }


function replsym( )

{


if ( kruti_text != "" )  
for ( input_symbol_idx = 0;   input_symbol_idx < kruti_array_length;    input_symbol_idx++ )

{

idx = 0  ;  

while (idx != -1 ) 
{

kruti_text = kruti_text.replace( kruti_array[ input_symbol_idx ] , unicode_array[input_symbol_idx] )
idx = kruti_text.indexOf( kruti_array[input_symbol_idx] )

} 
} 
kruti_text = kruti_text.replace( /±/g , "Zं" ) ;


kruti_text = kruti_text.replace( /Æ/g , "र्f" ) ;  

var pi = kruti_text.indexOf( "f" )

while ( pi != -1 ) 
{
var cni = kruti_text.charAt( pi + 1 )
var ctbr = "f" + cni
kruti_text = kruti_text.replace( ctbr , cni + "ि" ) 
pi = kruti_text.search( /f/ , pi + 1 ) 

} 

kruti_text = kruti_text.replace( /Ç/g , "fa" ) ; 
kruti_text = kruti_text.replace( /É/g , "र्fa" ) ;

var pi = kruti_text.indexOf( "fa" )

while ( pi != -1 ) 
{
var cntip2 = kruti_text.charAt( pi + 2 )
var ctbr = "fa" + cntip2
kruti_text = kruti_text.replace( ctbr , cntip2 + "िं" ) 
pi = kruti_text.search( /fa/ , pi + 2 ) 

} 

kruti_text = kruti_text.replace( /Ê/g , "ीZ" ) ; 


var powe = kruti_text.indexOf( "ि्" ) 

while ( powe != -1 )  

{
var cntwe = kruti_text.charAt( powe + 2 )
var ctbr = "ि्" + cntwe 
kruti_text = kruti_text.replace( ctbr , "्" + cntwe + "ि" ) 
powe = kruti_text.search( /ि्/ , powe + 2 ) 

} 
matraslist = "अ आ इ ई उ ऊ ए ऐ ओ औ ा ि ी ु ू ृ े ै ो ौ ं : ँ ॅ" 
var rpos = kruti_text.indexOf( "Z" )

while ( rpos > 0 )  
{
pphr = rpos - 1 ;
var chtr = kruti_text.charAt( pphr )


while ( matraslist.match( chtr ) != null )  

{
pphr = pphr - 1 ;
chtr = kruti_text.charAt( pphr ) ;

} 


ctbr = kruti_text.substr ( pphr , ( rpos - pphr ) ) ;
rstr = "र्" + ctbr ; 
ctbr = ctbr + "Z" ;
kruti_text = kruti_text.replace( ctbr , rstr ) ;
rpos = kruti_text.indexOf( "Z" ) ;

} 

}

} 
function copyclipboard() {
   var copyText = document.querySelector("#project_name_hindi_unicode");
   copyText.select();
   copyText.setSelectionRange(0, 99999);
   document.execCommand("copy");
   alert("Text Copied To Clipboard.");
}
function cleartext() {
	document.getElementById("project_name_hindi").value = '';
	document.getElementById("project_name_hindi_unicode").value = '';
}