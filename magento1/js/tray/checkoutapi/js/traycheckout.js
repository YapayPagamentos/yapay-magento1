/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function identifyCreditCardTc(ccNumber){

    var eloRE = /^((((457393)|(431274)|(627780)|(636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})$/;
    var visaRE = /^4[0-9]{12}(?:[0-9]{3})?$/;
    var masterRE = /^((5[1-5][0-9]{14})$|^(2(2(?=([2-9]{1}[1-9]{1}))|7(?=[0-2]{1}0)|[3-6](?=[0-9])))[0-9]{14})$/;
    var amexRE = /^3[47][0-9]{13}$/;
    var discoverRE = /^6(?:011|5[0-9]{2})[0-9]{12}$/;
    var hiperRE = /^(606282\d{10}(\d{3})?)|^(3841\d{15})$/;
    var dinersRE = /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/;
    var jcbRE = /^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}|5[0-9]{14}))$/;
    var auraRE = /^50[0-9]{17}$/; 
    
    document.getElementById('tcPaymentMethod').value = "";
    
    try { document.getElementById('tcPaymentFlag3').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag4').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag2').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag5').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag15').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag20').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag18').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag19').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag25').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}

    if(eloRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '16';
        document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(visaRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '3';
        document.getElementById('tcPaymentFlag3').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(masterRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '4';
        document.getElementById('tcPaymentFlag4').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(amexRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '5';
        document.getElementById('tcPaymentFlag5').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(discoverRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '15';
        document.getElementById('tcPaymentFlag15').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(hiperRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '20';
        document.getElementById('tcPaymentFlag20').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(dinersRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '2';
        document.getElementById('tcPaymentFlag2').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(jcbRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '19';
        document.getElementById('tcPaymentFlag19').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }else if(auraRE.test(ccNumber)){
        document.getElementById('tcPaymentMethod').value = '18';
        document.getElementById('tcPaymentFlag18').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }

}

function selectCreditCardTc(idPaymentTC,pPrice,pathM){
    
    document.getElementById('tcPaymentMethod').value = "";
    
    try { document.getElementById('tcPaymentFlag3').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag4').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag2').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag5').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag15').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag20').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag18').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag19').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tcPaymentFlag25').className = 'tcPaymentFlag';} catch(err) { console.debug(err.message);}

    document.getElementById('tcPaymentMethod').value = idPaymentTC;
    document.getElementById('tcPaymentFlag'+idPaymentTC).className = 'tcPaymentFlag tcPaymentFlagSelected';
    
    getSplitValues(pPrice, idPaymentTC, pathM);
}

function selectTefTc(idPaymentTC){
    
    document.getElementById('tctPaymentMethod').value = "";
    
    try { document.getElementById('tctPaymentFlag7').className = 'tctPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tctPaymentFlag14').className = 'tctPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tctPaymentFlag22').className = 'tctPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tctPaymentFlag23').className = 'tctPaymentFlag';} catch(err) { console.debug(err.message);}
    try { document.getElementById('tctPaymentFlag21').className = 'tctPaymentFlag';} catch(err) { console.debug(err.message);}

    document.getElementById('tctPaymentMethod').value = idPaymentTC;
    document.getElementById('tctPaymentFlag'+idPaymentTC).className = 'tctPaymentFlag tctPaymentFlagSelected';
}

function getSplitValues(pPrice, pMethod, pathM){
    
    if (pMethod != ""){
        document.getElementById('traycheckoutapi_split').innerHTML = "<option value=\"\">Carregando ...</option>";
        var data_file = pathM+"checkoutapi/standard/getsplit/price/"+pPrice+"/method/"+pMethod+"/type/standard/";

        var http_request = new XMLHttpRequest();
        try{
           http_request = new XMLHttpRequest();
        }catch (e){
           try{
              http_request = new ActiveXObject("Msxml2.XMLHTTP");
           }catch (e) {
              try{
                 http_request = new ActiveXObject("Microsoft.XMLHTTP");
              }catch (e){
                 console.debug("Your browser broke!");
                 return false;
              }

           }
        }

        http_request.onreadystatechange = function(){
           if (http_request.readyState == 4){
              var jsonObj = JSON.parse(http_request.responseText);
              document.getElementById('traycheckoutapi_split').innerHTML = "";
              var optionSplit = "";
              for(var key in jsonObj){
                  optionSplit += "<option value='"+key+"'>"+jsonObj[key]+"</option>";
              }
              document.getElementById('traycheckoutapi_split').innerHTML = optionSplit;
              document.getElementById('traycheckoutapi_split_value').value = jsonObj[1].replace(/.*R\$/,'').replace(/\,/,'.');
           }
        }

        http_request.open("GET", data_file, true);
        http_request.send();
    }
}

