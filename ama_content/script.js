
function displayError(language_param){
  closeInfoWindow();
  closePaymentWindow();
  if (language_param === 'fr'){
    document.getElementById("infoWindow").style.display = "block";
    document.getElementById('error_fr').style.display = "block";
  }else{
    document.getElementById("infoWindow").style.display = "block";
    document.getElementById('error_en').style.display = "block";
  }
  hideLoadingOverlay(document.getElementById('loadingOverlay'));
}

function displayInfo(language_param){
  if (language_param === 'fr'){
    document.getElementById("infoWindow").style.display = "block";
    document.getElementById("info_content_fr").style.display = "block";
    document.getElementById("info_content_en").style.display = "none";
  }else{
    document.getElementById("infoWindow").style.display = "block";
    document.getElementById("info_content_fr").style.display = "none";
    document.getElementById("info_content_en").style.display = "block";
  }


}

function isANumber(msisdn_param){
  if(!/[a-zA-Z]/.test(msisdn_param)){
    var msisdn = parseInt(msisdn_param);
    if (msisdn > 0){
      return true;
    }else{
      return false;
    }
  }else{
    return false;
  }
  
}

function ama_getCurrency(){
  fetch('/wp-json/ama/v1/currency')
    .then(response=> response.json())
    .then(data => {
      document.getElementById("currency_fr").textContent = data["Currency"];
      document.getElementById("currency_en").textContent = data["Currency"];
    })
    .catch(error => {
      console.error('Error fetching data:', error);
    });

}

function ama_getKyc(msisdn_param, language_param){
    const url = 'wp-json/ama/v1/kyc';
    const data = {
      msisdn: msisdn_param
    };
    displayInfo(language_param);
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(result => {
        var result_json = JSON.parse(result);
        if (!result_json["isBarred"] && result_json["isPinSet"]){
          document.getElementById("ama_last_name_en").textContent = result_json["lastName"] + ' ' + result_json["firstName"];
          document.getElementById("ama_last_name_fr").textContent = result_json["lastName"] + ' ' + result_json["firstName"];
          document.getElementById("ama_last_name_info_en").textContent = result_json["lastName"] + ' ' + result_json["firstName"];
          document.getElementById("ama_last_name_info_fr").textContent = result_json["lastName"] + ' ' + result_json["firstName"];
          
        }else{
          displayError(language_param);
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
}

function populatePaymentInfo(language_param){
  if(language_param === 'fr'){
    document.getElementById("payment-content-fr").style.display = "block";
  }else{
    document.getElementById("payment-content-en").style.display = "block";
  }
}

function ama_doPayment(msisdn_param, amount_param, reference_param) {
  fetch('/wp-json/ama/v1/payment', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify({
          "msisdn": msisdn_param,
          "amount": amount_param,
          "reference": reference_param,
      })
  })
  .then(response => response.json())
  .then(data => {
      data_json = JSON.parse(data);
      document.getElementById('internal_id_fr').textContent = data_json["internal_id"];
      document.getElementById('internal_id_en').textContent = data_json["internal_id"];
      hideLoadingOverlay(document.getElementById('loadingOverlay'));
  })
  .catch(error => {
      console.error('Error:',error);
  });
  
}

function fetchTransactionStatus(internalId) {
  var targetDate = new Date();
  var localOffset = targetDate.getTimezoneOffset();
  var hoursToAdd = -Math.floor(localOffset / 60);

  fetch('/wp-json/ama/v1/transaction', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ "internal_id": internalId })
  })
  .then(response => response.json())
  .then(data => {
      // Process the response data
    const temp_data = JSON.parse(data);
    document.getElementById("ama_msisdn_fr").textContent = temp_data.msisdn;
    document.getElementById("ama_amount_fr").textContent = temp_data.amount;
    document.getElementById("ama_reference_fr").textContent = temp_data.reference;
    document.getElementById("ama_internal_id_fr").textContent = temp_data.internal_id;
    document.getElementById("ama_id_fr").textContent = temp_data.am_id;
    document.getElementById("ama_message_fr").textContent = temp_data.message;
    document.getElementById("ama_status_fr").textContent = temp_data.status;
    document.getElementById("ama_response_code_fr").textContent = temp_data.response_code;
    document.getElementById("ama_base_url_fr").textContent = temp_data.base_url;
    document.getElementById("ama_msisdn_en").textContent = temp_data.msisdn;
    document.getElementById("ama_amount_en").textContent = temp_data.amount;
    document.getElementById("ama_reference_en").textContent = temp_data.reference;
    document.getElementById("ama_internal_id_en").textContent = temp_data.internal_id;
    document.getElementById("ama_id_en").textContent = temp_data.am_id;
    document.getElementById("ama_message_en").textContent = temp_data.message;
    document.getElementById("ama_status_en").textContent = temp_data.status;
    document.getElementById("ama_response_code_en").textContent = temp_data.response_code;
    document.getElementById("ama_base_url_en").textContent = temp_data.base_url;
    hideLoadingOverlay(document.getElementById('loadingOverlay'));
    closeInfoWindow();
  })
  .catch(error => {
    hideLoadingOverlay(document.getElementById('loadingOverlay'));
    closeInfoWindow();
  });
}

function displayPaymentInformation(){
  showLoadingOverlay(document.getElementById('loadingOverlay'));
  var amount = document.getElementById("transaction_amount").textContent.trim().replace(/\s/g, "");
  var country = navigator.language.substring(0,2);
  var siteName = document.title;
  var product = document.getElementById("product_code").textContent;
  var msisdnInputElement = document.getElementById("ama_msisdn");
  var msisdnInput = msisdnInputElement.value;

  

  document.getElementById("amount_fr").textContent = amount;
  document.getElementById("amount_en").textContent = amount;
  document.getElementById("siteName_fr").textContent = siteName;
  document.getElementById("siteName_en").textContent = siteName;
  document.getElementById("product_id_fr").textContent = product;
  document.getElementById("product_id_en").textContent = product;
  
  
  if (isANumber(msisdnInput)){
    msisdnInput = parseInt(msisdnInput);
    document.getElementById("phone_number_fr").textContent = msisdnInput;
    document.getElementById("phone_number_en").textContent = msisdnInput;
    
    displayInfo(country);
    ama_getCurrency();
    
    ama_getKyc(msisdnInput, country);

    ama_doPayment(msisdnInput, amount, product);

  }else {
    displayError(country);
  }
}

function showNext(){
  showLoadingOverlay(document.getElementById('loadingOverlay'));
  var country = navigator.language.substring(0,2);
  var internal_id= "";
  var lang = 'en'
  if (country === "fr"){
    lang = 'fr';
  }else{
    lang = 'en';
  }

  document.getElementById("paymentWindow").style.display = "block";
  
  internal_id = document.getElementById("internal_id_"+lang).textContent;
  document.getElementById("payment-content-"+lang).style.display = "block";
  fetchTransactionStatus(internal_id);
  var typeNumber = 25;
  var errorCorrectionLevel = 'H';
  var qr = qrcode(typeNumber, errorCorrectionLevel);
  qr.addData(document.getElementById("ama_id_"+lang).textContent);
  qr.make();
  document.getElementById('ama_qr_code_'+lang).innerHTML = qr.createImgTag();
}

function showPrevious(){
  var country = navigator.language.substring(0,2);
  closePaymentWindow();
  displayInfo(country);
}

function downloadPaymentInfo(div_param, file_name_param) {
  window.jsPDF = window.jspdf.jsPDF;
  const file_name = document.getElementById(file_name_param).textContent || 'default';
  html2canvas(document.getElementById(div_param)).then((canvas) => {
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('l', 'pt', 'a4');
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = pdf.internal.pageSize.getHeight();
    const ratio = imgProps.width / imgProps.height;
    let imgWidth, imgHeight;
    if (ratio > 1) {
      imgWidth = pdfWidth - 20; // Subtracting left and right margins
      imgHeight = imgWidth / ratio;
    } else {
      imgHeight = pdfHeight - 20; // Subtracting left and right margins
      imgWidth = imgHeight * ratio;
    }
    const x = (pdfWidth - imgWidth) / 2;
    const y = (pdfHeight - imgHeight) / 2;
    pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
    pdf.save(file_name + '.pdf');
  });
}

function closePaymentWindow(){
  document.getElementById("paymentWindow").style.display = "none";
  document.getElementById("payment-content-fr").style.display = "none";
  document.getElementById("payment-content-en").style.display = "none";
  document.getElementById('error_en').style.display = "none";
  document.getElementById('error_fr').style.display = "none";
  hideLoadingOverlay(document.getElementById('loadingOverlay'));
}

function closeInfoWindow() {
  document.getElementById("infoWindow").style.display = "none";
  document.getElementById("info_content_fr").style.display = "none";
  document.getElementById("info_content_en").style.display = "none";
  document.getElementById('error_en').style.display = "none";
  document.getElementById('error_fr').style.display = "none";
  hideLoadingOverlay(document.getElementById('loadingOverlay'));
}

// Loading section

function showLoadingOverlay(loadingElement) {
  loadingElement.style.display = 'block';
  console.log("SHOW!");
}

function hideLoadingOverlay(loadingElement) {
  loadingElement.style.display = 'none';
  console.log("HIDE!");
}
