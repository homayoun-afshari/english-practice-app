window.addEventListener('load', ()=> {
	const elmMain = document.getElementById('main');
	elmMain.scaleOld = 0;
	elmMain.getScale = ()=> {
		const elmCheckBoxes = document.getElementsByClassName('checkBox');
		let scale = 0;
		for (let i = 0; i < elmCheckBoxes.length; i++) {
			if (elmCheckBoxes[i].classList.contains('checked'))
				scale = Math.max(scale, elmCheckBoxes[i].dataset['scale']);
		}
		return scale;
	};
	elmMain.setTotal = () => {
		const elmInfTotal = document.getElementById('infTotal');
		const elmInfTotalValue = elmInfTotal.getElementsByClassName('value')[0];
		const elmSpcTotal = document.getElementById('spcTotal');
		const total = elmSpcTotal.value;
		elmInfTotal.dataset['total'] = total;
		elmInfTotalValue.innerText = total;
	};
	elmMain.setTime = () => {
		const elmInfTime = document.getElementById('infTime');
		const elmInfTimeValue = elmInfTime.getElementsByClassName('value')[0];
		const elmSpcTotal = document.getElementById('spcTotal');
		const elmSpcSeconds = document.getElementById('spcSeconds');
		const timeSet = Math.round(elmSpcTotal.value*elmSpcSeconds.value, 2);
		const minutes = Math.floor(timeSet/60);
		const seconds = timeSet % 60;
		elmInfTime.dataset['timeSet'] = timeSet;
		elmInfTimeValue.innerText = minutes.toString().padStart(2, '0')+':'+seconds.toString().padStart(2, '0');
	};
	elmMain.setTotal();
	elmMain.setTime();
	
	elmCheckBoxes = document.getElementsByClassName('checkBox');
	for (let i = 0; i < elmCheckBoxes.length; i++) {
		elmCheckBoxes[i].addEventListener('click', ()=> {
			const elmSpcSeconds = document.getElementById('spcSeconds');
			const elmBtnStart = document.getElementById('btnStart');
			let scaleNew;
			if (elmCheckBoxes[i].classList.contains('checked')) 
				elmCheckBoxes[i].classList.remove('checked');
			else {
				elmCheckBoxes[i].classList.add('checked');
			}
			scaleNew = elmMain.getScale();
			if (scaleNew === 0)
				elmBtnStart.classList.add('hidden');
			else {
				elmBtnStart.classList.remove('hidden');
				elmSpcSeconds.value *= scaleNew/elmMain.scaleOld;
				elmMain.scaleOld = scaleNew;
				elmSpcSeconds.dispatchEvent(new Event('input'));
			}
		});
		elmMain.scaleOld = elmMain.getScale();
	}
	
	const elmSpcTotal = document.getElementById('spcTotal');
	elmSpcTotal.callback = ()=> {
		elmMain.setTotal();
		elmMain.setTime();
	};
	elmSpcTotal.addEventListener('input', elmSpcTotal.callback);
	elmSpcTotal.addEventListener('paste', elmSpcTotal.callback);
	elmSpcTotal.addEventListener('focusout', () => {
		elmSpcTotal.value = Math.round(elmSpcTotal.value, 0);
		if (parseInt(elmSpcTotal.value) < parseInt(elmSpcTotal.min))
			elmSpcTotal.value = elmSpcTotal.min;
		if (parseInt(elmSpcTotal.value) > parseInt(elmSpcTotal.max))
			elmSpcTotal.value = elmSpcTotal.max;
		elmSpcTotal.callback();
	});
	
	const elmSpcSeconds = document.getElementById('spcSeconds');
	elmSpcSeconds.callback = ()=> {
		elmMain.setTime();
	};
	elmSpcSeconds.addEventListener('input', elmSpcSeconds.callback);
	elmSpcSeconds.addEventListener('paste', elmSpcSeconds.callback);
	elmSpcSeconds.addEventListener('focusout', () => {
		if (parseInt(elmSpcSeconds.value) < parseInt(elmSpcSeconds.min))
			elmSpcSeconds.value = elmSpcSeconds.min;
		elmSpcSeconds.callback();
	});
	
	
	const elmBtnStart = document.getElementById('btnStart');
	elmBtnStart.addEventListener('click', (event)=> {
		event.preventDefault();
		elmMain.showMessage('Are you sure you want to start the test?', ()=> {
			const elmCheckBoxes = document.getElementsByClassName('checkBox');
			const elmInfTotal = document.getElementById('infTotal');
			const elmInfTime = document.getElementById('infTime');
			const elmSpcTotal = document.getElementById('spcTotal');
			const elmSpcSeconds = document.getElementById('spcSeconds');
			let items = [];
			for (let i = 0; i < elmCheckBoxes.length; i++) {
				const temp = elmCheckBoxes[i].id.split('spcItem');
				if (temp.length > 1 && elmCheckBoxes[i].classList.contains('checked'))
					items.push(temp[1].toLowerCase());
			}
			const query = new URLSearchParams({
				'items': JSON.stringify(items),
				'total': elmInfTotal.dataset['total'],
				'timeSet': elmInfTime.dataset['timeSet']
			});
			window.open('test.php?'+query.toString(), '_self');
		});
	});
});