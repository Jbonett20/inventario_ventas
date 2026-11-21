var $ = (function(){
    function el(){ var o = {}; ['val','show','hide','text','prop','toggleClass','html','after','on','modal','removeClass','addClass','attr','append','empty','click','focus','ready'].forEach(function(k){ o[k] = function(){ return o; }; }); o.closest = function(){ return o; }; o.find = function(){ return { length: 0 }; }; o.data = function(){ return null; }; o.is = function(){ return false; }; o.each = function(){}; return o; }
    var f = function(){ return el(); };
    f.each = function(arr, fn){ if (arr && arr.length !== undefined) { for (var i = 0; i < arr.length; i++) fn(i, arr[i]); } };
    f.getJSON = function(){};
    return f;
})();
function escHtml2(s){ return String(s == null ? '' : s); }
function formatoNumero(n){ return String(n); }
var PNotify = { error: function(){}, success: function(){}, info: function(){} };
var BASE_URL = '';
var carrito = [];
var idCounter = 0;
var totalGanancia = 0;
function calcularCambio(){}
