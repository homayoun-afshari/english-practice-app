window.addEventListener('load', ()=> {
	const elmMain = document.getElementById('main');
	elmMain.setMessage = ()=> {
		const elmMessage = document.getElementById('message');
		const rect = elmMain.getBoundingClientRect();
		elmMessage.style.left = (rect.left-5).toString()+'px';
		elmMessage.style.top = (rect.top-5).toString()+'px';
		elmMessage.style.width = (rect.width+10).toString()+'px';
		elmMessage.style.height = (rect.height+10).toString()+'px';
	};
	elmMain.showMessage = (message, func)=> {
		const elmMessage = document.getElementById('message');
		const elmMessageBody = elmMessage.getElementsByClassName('body')[0];
		const elmText = elmMessageBody.getElementsByClassName('text')[0];
		const elmBtnYes = document.getElementById('btnYes');
		elmMessage.classList.remove('hidden');
		elmMain.setMessage();
		elmText.innerText = message;
		elmBtnYes.run = func;
	};
	
	
	const elmMessage = document.getElementById('message');
	const elmMessageBody = elmMessage.getElementsByClassName('body')[0];
	elmMessage.addEventListener('click', ()=> {
		elmMessage.classList.add('hidden');
	});
	elmMessageBody.addEventListener('click', (event)=> {
		event.stopPropagation();
	});


	const elmBtnNo = document.getElementById('btnNo');
	elmBtnNo.addEventListener('click', ()=> {
		const elmMessage = document.getElementById('message');
		elmMessage.click();
	});
	
	const elmBtnYes = document.getElementById('btnYes');
	elmBtnYes.addEventListener('click', ()=> {
		elmBtnYes.run();
	});
});

window.addEventListener('resize', ()=> {
	const elmMain = document.getElementById('main');
	elmMain.setMessage();
});

function ucfirst(string) {
	return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}
		
async function postData(dataOut) {
  const response = await fetch('backend/receiver.php', {
    'method': 'POST',
    'headers': {
      'Content-Type': 'application/json'
    },
    'body': JSON.stringify(dataOut),
  });
  return response.json();
}