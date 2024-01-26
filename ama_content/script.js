
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
    document.getElementById("ama_message_fr").textContent = temp_data.message;
    document.getElementById("ama_status_fr").textContent = temp_data.status;
    document.getElementById("ama_response_code_fr").textContent = temp_data.response_code;
    document.getElementById("ama_base_url_fr").textContent = temp_data.base_url;
    document.getElementById("ama_msisdn_en").textContent = temp_data.msisdn;
    document.getElementById("ama_amount_en").textContent = temp_data.amount;
    document.getElementById("ama_reference_en").textContent = temp_data.reference;
    document.getElementById("ama_internal_id_en").textContent = temp_data.internal_id;
    document.getElementById("ama_message_en").textContent = temp_data.message;
    document.getElementById("ama_status_en").textContent = temp_data.status;
    document.getElementById("ama_response_code_en").textContent = temp_data.response_code;
    document.getElementById("ama_base_url_en").textContent = temp_data.base_url;
    if (typeof temp_data.am_id !== 'undefined' && temp_data.am_id !== null){
      document.getElementById("ama_id_fr").textContent = temp_data.am_id;
      document.getElementById("ama_id_en").textContent = temp_data.am_id;
    }else{
      document.getElementById("ama_id_fr").textContent = "NA";
      document.getElementById("ama_id_en").textContent = "NA";
    }

    hideLoadingOverlay(document.getElementById('loadingOverlay'));
    closeInfoWindow();
  })
  .catch(error => {
    console.error('Error:', error);
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
  qr.addData(document.getElementById("internal_id_"+lang).textContent);
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
  var infoWindow = document.getElementById("infoWindow");
  var info_content_fr = document.getElementById("info_content_fr");
  var info_content_en = document.getElementById("info_content_en");
  var info_error_en = document.getElementById('error_en');
  var info_error_fr = document.getElementById('error_fr');
  if (!infoWindow == null){
    infoWindow.style.display = "none";  
  }

  if (!info_content_fr == null){
    info_content_fr.style.display = "none";
  }

  if (!info_content_en == null){
    info_content_en.style.display = "none";
  }

  if (!info_error_fr == null){
    info_error_fr.style.display = "none";
  }

  if (!info_error_en == null){
    info_error_en.style.display = "none";
  }
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

function fetchTransactionList(msisdn_param, internal_id_param) {
  console.log(msisdn_param);
  var data = {
    msisdn: msisdn_param,
    internal_id: internal_id_param
  };
  
  fetch("/wp-json/ama/v1/lists_transaction", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(data)
  })
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      populate_transaction_list(data);
    })
    .catch(function(error) {
      console.log("Error:", error);
    });
}

function populate_transaction_list(json_list){
  var container = document.getElementById('ama_list_transaction');
  container.innerHTML = "";
  if(json_list[0].msisdn == null){
    var gridItem = document.createElement('div');
    gridItem.classList.add('ama-grid-item');
    var emptyMessage = document.createElement('span');
    emptyMessage.classList.add('ama_empty_message');
    emptyMessage.textContent = '***ANY TRANSACTION AVAILABLE***';
    gridItem.appendChild(emptyMessage);
    container.appendChild(gridItem);
    hideLoadingOverlay(document.getElementById('loadingOverlay'));
    return false;
  }
  for (var i = 0; i < json_list.length; i++) {
    var item = json_list[i];

    var gridItem = document.createElement('div');
    gridItem.classList.add('ama-grid-item');
    if (item['msisdn'] == null){
      continue;
    }
    for (var prop in item) {
      if (prop == 'code' || prop == 'success' || prop == "transaction_type"){
        continue;
      }else{
        if (item.hasOwnProperty(prop)) {
          var textDiv = document.createElement('p');
          textDiv.classList.add('ama_text_div');

          var properties = document.createElement('span');
          properties.classList.add('ama_prop');

          var text_value = document.createElement('span');

          properties.textContent = transformText(prop) + ":";
          if(prop == "internal_id" || prop == "am_id"){
            text_value.classList.add('ama_text_value'); 
            text_value.classList.add('ama_internal_id');
          }else{
            text_value.classList.add('ama_text_value');
          }
          text_value.textContent = item[prop];
          textDiv.appendChild(properties);
          textDiv.appendChild(text_value);
          gridItem.appendChild(textDiv);
        }
      }
    }
    var refreshButton = document.createElement('button');
    refreshButton.textContent = "Verify";
    refreshButton.classList.add('ama_button');
    var divButton = document.createElement('div');
    divButton.classList.add('ama_div_button');
    var showButton = document.createElement('button');
    showButton.textContent = "Show";
    showButton.classList.add("ama_button");
    showButton.addEventListener('click', show_transactions_details);
    refreshButton.addEventListener('click', verify_transactions_details)
    divButton.appendChild(showButton);
    
    if (item['response_code'] == 'DP00800001006'){
      divButton.appendChild(refreshButton);  
    }
    
    gridItem.appendChild(divButton);
    container.appendChild(gridItem);
  }
  hideLoadingOverlay(document.getElementById('loadingOverlay'));
}

function transaction_list() {

  showLoadingOverlay(document.getElementById('loadingOverlay'));
  const input_msisdn = document.getElementById("ama_msisdn").value;
  const input_internal_id = document.getElementById("ama_internal_id").value;
  let input_msisdn_is_number = isANumber(input_msisdn);
  document.getElementById('ama_list_transaction').innerHTML = "";
  var data = fetchTransactionList(input_msisdn, input_internal_id);
  
  
}

function show_transaction_info(internal_id){
  showLoadingOverlay(document.getElementById('loadingOverlay'));
  var country = navigator.language.substring(0,2);
  var lang = 'en'
  if (country === "fr"){
    lang = 'fr';
  }else{
    lang = 'en';
  }

  document.getElementById("paymentWindow").style.display = "block";
  document.getElementById("payment-content-"+lang).style.display = "block";
  
  fetchTransactionStatus(internal_id);
  var typeNumber = 25;
  var errorCorrectionLevel = 'H';
  var qr = qrcode(typeNumber, errorCorrectionLevel);
  console.log(lang);
  qr.addData(internal_id);
  qr.make();
  document.getElementById('ama_qr_code_'+lang).innerHTML = qr.createImgTag();
}

function show_transactions_details(param1){
  var divElement = this.closest(".ama-grid-item");
  var textValueElement = divElement.querySelector(".ama_internal_id");
  var code = textValueElement.innerText;
  show_transaction_info(code);
}

function verify_transactions_details(param1){
  var divElement = this.closest(".ama-grid-item");
  var textValueElement = divElement.querySelector(".ama_internal_id");
  var code = textValueElement.innerText;
  show_transaction_info(code);
}

function transformText(text) {
  // Replace underscores with spaces
  var replacedText = text.replace(/_/g, ' ');

  // Capitalize the words and convert to full uppercase
  var capitalizedText = replacedText.replace(/(?:^|\s)\S/g, function(char) {
    return char.toUpperCase();
  }).toUpperCase();

  return capitalizedText;
}