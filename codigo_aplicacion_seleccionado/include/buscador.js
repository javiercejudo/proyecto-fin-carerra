var xmlhttp;
var ir_arriba=1;
var ini_g=0;
var primera=0;

function showHint(str,elt,lista,ini) {
	if (str.length==0) {
	  document.getElementById(elt).innerHTML="";
	  return;
	}
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null) {
	  alert ("Your browser does not support XMLHTTP!");
	  return;
	}
	var url="include/"+lista+".php";
	url=url+"?q="+str.trim();
	url=url+"&ini="+ini;
	url=url+"&sid="+Math.random();
	xmlhttp.onreadystatechange=stateChanged;
	xmlhttp.open("GET",url,true);
	xmlhttp.send(null);
}

function mostrarResultados(elt,ini){
	var q = elt.value;
	showHint(q,"searchResults","buscador",ini);
	if(q!="") document.getElementById("searchResults").style.display="block";
	else document.getElementById("searchResults").style.display="none";
}

function stateChanged() {
	if (xmlhttp.readyState==4) {
	  document.getElementById("searchResults").innerHTML=xmlhttp.responseText;
	  document.getElementById("buscando").src="imagenes/loading2.png";
	  setTimeout("document.getElementById('searchResults').style.opacity='1'",0);
	  document.getElementById('searchResults').style.filter='alpha(opacity=100)';
	  if(ir_arriba==1) window.scrollTo(currentXPosition(),0);
	  else ir_arriba=1;
	}
}

function actualizarResultados (i) {
	if (document.getElementById('searchBox').value.trim().split('-')[0].trim().length>0) {
		ir_arriba=0;
		setTimeout("mostrarResultados(document.getElementById('searchBox'),"+ini_g+")",33);
	}
	
	var t=setTimeout("actualizarResultados(0)",25000);
}
function searchBoxFocus(elt){
	elt.style.border='2px solid #EE2B27';
	if(elt.value.trim()!=location.hash.substr(3).trim()) {
		searchBoxChange(elt);
		searchBoxUp(elt,0);
	}
	elt.hasFocus=true;
}
function searchBoxFocusB(elt){
	if(location.hash.length>0)
		elt.value=location.hash.substr(3);
	else if ("<?echo isset($q)?>")
		elt.value="<?echo $q?>";
	elt.style.border='2px solid #EE2B27';
	elt.focus();
	elt.hasFocus=true;
	searchBoxUp(elt,0);
	actualizarResultados(1);
}
function searchBoxBlur(elt){
	elt.style.border='2px solid #cccccc';
	elt.hasFocus=false;
}
function searchBoxChange(elt){
	if (elt.value.trim()!=location.hash.substr(3)) {
		location.hash='#b='+document.getElementById('searchBox').value.trim();
	}
}
function searchBoxUp(elt,ini){
	ini_g=ini;
	if(elt.value.trim().split('-')[0].trim().length<1){
		if(primera!=0) {
			document.getElementById('searchResults').style.display='none';
			document.getElementById('searchResults').innerHTML='';
		}
		document.getElementById("buscando").src="imagenes/loading2.png";
	} else {
		document.getElementById("buscando").src="imagenes/loading.gif";
		document.getElementById('searchResults').style.opacity='0.6';
		document.getElementById('searchResults').style.filter='alpha(opacity=60)';
		mostrarResultados(elt,ini);
		primera=1;
		//window.scrollTo(currentXPosition(),0);
		//arriba();
	}
}
function teclas(event) {
	var numeroPorPagina= Number(document.getElementById('numeroPorPagina').value);
	var inp = document.getElementById('searchBox');
	if (event.keyCode==190 && inp.hasFocus==false) {
		inp.focus();
		arriba();				
	} else if (event.keyCode==27 && inp.hasFocus==true) {
		inp.blur();
	}
	var d = document.getElementById('searchBox');
	var max = document.getElementById('numberOfResults').value;	

	if (event.keyCode==39 && ini_g<max-numeroPorPagina && inp.hasFocus==false) {
		ini_g+=numeroPorPagina;
		searchBoxUp(d,ini_g);
	} else if (event.keyCode==37 && ini_g!=0 && inp.hasFocus==false) {
		ini_g-=numeroPorPagina;
		searchBoxUp(d,ini_g);
	}
}
function arriba () {
	var inicio = currentYPosition();
	var destino = inicio-inicio/(8);
	//alert(inicio+" "+destino);
	if(destino < 0) destino=0;
	if(destino>1) setTimeout("window.scrollTo(currentXPosition(),"+destino+");arriba()",15);
	else window.scrollTo(currentXPosition(),0);
}
function currentYPosition() {
	// Firefox, Chrome, Opera, Safari
	if (self.pageYOffset) return self.pageYOffset;
	// Internet Explorer 6 - standards mode
	if (document.documentElement && document.documentElement.scrollTop)
		return document.documentElement.scrollTop;
	// Internet Explorer 6, 7 and 8
	if (document.body.scrollTop) return document.body.scrollTop;
	return 0;
}
function currentXPosition() {
	// Firefox, Chrome, Opera, Safari
	if (self.pageXOffset) return self.pageXOffset;
	// Internet Explorer 6 - standards mode
	if (document.documentElement && document.documentElement.scrollTop)
		return document.documentElement.scrollLeft;
	// Internet Explorer 6, 7 and 8
	if (document.body.scrollTop) return document.body.scrollLeft;
	return 0;
}

function elmYPosition(eID) {
	var elm = document.getElementById(eID);
	var y = elm.offsetTop;
	var node = elm;
	while (node.offsetParent && node.offsetParent != document.body) {
		node = node.offsetParent;
		y += node.offsetTop;
	} return y;
}

//-------------------------------------------------------------------

String.prototype.trim = function () {
	return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

function GetXmlHttpObject() {
	if (window.XMLHttpRequest) {
	  // code for IE7+, Firefox, Chrome, Opera, Safari
	  return new XMLHttpRequest();
	}
	if (window.ActiveXObject) {
	  // code for IE6, IE5
	  return new ActiveXObject("Microsoft.XMLHTTP");
	}
	return null;
}
