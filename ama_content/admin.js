jQuery(document).ready(function($) {

    fetch('/wp-json/ama/v1/transaction_summary')
    .then(response => response.json())
    .then(data => {
        const temp_data = JSON.parse(data);

        const details = temp_data.details;

        const total = Object.values(details).reduce((acc, val) => acc + parseInt(val), 0);

        const labels = Object.keys(details);
        const values = Object.values(details);
        
        const ctx = document.getElementById('ama_pieChart_summary').getContext('2d');
        new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
            data: values,
            backgroundColor: ['#00FF00', '#FF0000', '#0000FF', '#FFFF00']
            }]
        },
        options: {
            responsive: true
        }
        });
    });

    fetch('/wp-json/ama/v1/success_transaction')
    .then(response => response.json())
    .then(data => {
        data_json = JSON.parse(data);
        const table = document.getElementById('ama_transaction_table');
        for(let i = 0; i<data_json.length; i++){
            const row = table.insertRow();
            row.insertCell().textContent = data_json[i].msisdn;
            row.insertCell().textContent = data_json[i].amount;
            row.insertCell().textContent = data_json[i].reference;
            row.insertCell().textContent = data_json[i].internal_id;
            row.insertCell().textContent = data_json[i].am_id;
            row.insertCell().textContent = data_json[i].status;
            row.insertCell().textContent = data_json[i].response_code;
            row.insertCell().textContent = data_json[i].base_url;
            row.insertCell().textContent = data_json[i].transaction_date;
            if(i === 49){break;}
        }
        
    });

});

function ama_search_items(){
    showLoadingOverlay(document.getElementById('loadingOverlay'));
    const url = '/wp-json/ama/v1/transaction_by';
    const input_search = document.getElementById("ama_search_input");
    const data = { 'input': input_search.value };

    fetch(url, {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        let result_json = JSON.parse(result);
        if (result_json.length == 0){
            alert("No data found!");
        }else{
            const table_populate = document.getElementById('ama_transactions_found');
            table_populate.innerHTML = "";
    
            const thead = document.createElement('thead');
            const tr = document.createElement('tr');
            
            const headerTexts = ['MSISDN', 'Amount', 'Reference', 'Internal ID', 'AM ID', 'Status', 'Response Code', 'Base URL', 'Transaction Date'];
    
            headerTexts.forEach(text => {
                const th = document.createElement('th');
                th.textContent = text;
                tr.appendChild(th);
            });
    
            thead.appendChild(tr);
    
            table_populate.appendChild(thead);
                
            for(let i = 0; i<result_json.length; i++){
                const row = table_populate.insertRow();
                row.insertCell().textContent = result_json[i].msisdn;
                row.insertCell().textContent = result_json[i].amount;
                row.insertCell().textContent = result_json[i].reference;
                row.insertCell().textContent = result_json[i].internal_id;
                row.insertCell().textContent = result_json[i].am_id;
                row.insertCell().textContent = result_json[i].status;
                row.insertCell().textContent = result_json[i].response_code;
                row.insertCell().textContent = result_json[i].base_url;
                row.insertCell().textContent = result_json[i].transaction_date;
            }
        }
        hideLoadingOverlay(document.getElementById('loadingOverlay'));
    })
    .catch(error => {
        hideLoadingOverlay(document.getElementById('loadingOverlay'));
        console.error(error);
    });
}

// Loading section

function showLoadingOverlay(loadingElement) {
    loadingElement.style.display = 'block';
  }
  
  function hideLoadingOverlay(loadingElement) {
    loadingElement.style.display = 'none';
  }
  
  // End of Loading Section



