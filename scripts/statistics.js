window.addEventListener('load', ()=> {
	const elmMain = document.getElementById('main');
	elmMain.setDummies = ()=> {
		const elmStats = document.getElementsByClassName('stat');
		for (let i = 0; i < elmStats.length; i++) {
			const elmContent = elmStats[i].getElementsByClassName('content')[0];
			const elmDummy = elmContent.getElementsByClassName('dummy')[0];
			const elmBars = elmContent.getElementsByClassName('bar');
			let width = parseFloat(getComputedStyle(elmContent).getPropertyValue('padding-left'));
			width -= parseFloat(getComputedStyle(elmDummy).getPropertyValue('margin-left'));
			for (let j = 0; j < elmBars.length; j++) {
				width += parseFloat(getComputedStyle(elmBars[j]).getPropertyValue('margin-left'));
				width += elmBars[j].getBoundingClientRect()['width'];
				width += parseFloat(getComputedStyle(elmBars[j]).getPropertyValue('margin-right'));
			}
			width += parseFloat(getComputedStyle(elmContent).getPropertyValue('padding-right'));
			width -= parseFloat(getComputedStyle(elmDummy).getPropertyValue('margin-right'));
			elmDummy.style.width = width.toString()+'px';
		}
	};
	elmMain.disableBars = ()=> {
		const elmBars = document.getElementsByClassName('bar');
		if (elmBars.length === 0)
			return;
		const elmInfTestId = document.getElementById('infTestId');
		const elmInfTestIdValue = elmInfTestId.getElementsByClassName('value')[0];
		const elmInfScore = document.getElementById('infScore');
		const elmInfScoreIdValue = elmInfScore.getElementsByClassName('value')[0];
		const elmInfTimestamp = document.getElementById('infTimestamp');
		const elmInfTimestampValue = elmInfTimestamp.getElementsByClassName('value')[0];
		const elmBtnReview = document.getElementById('btnReview');
		for (let i = 0; i < elmBars.length; i++)
			elmBars[i].classList.remove('selected');
		elmInfTestIdValue.innerHTML = '&#8709;';
		elmInfScoreIdValue.innerHTML = '&#8709;';
		elmInfTimestampValue.innerHTML = '&#8709;';
		elmBtnReview.classList.add('hidden');
	}
	elmMain.addEventListener('click', ()=> {
		elmMain.disableBars();
	});
	elmMain.setDummies();

	const elmStats = document.getElementsByClassName('stat');
	for (let i = 0; i < elmStats.length; i++) {
		const elmContent = elmStats[i].getElementsByClassName('content')[0];
		const elmBars = elmContent.getElementsByClassName('bar');
		for (let j = 0; j < elmBars.length; j++) {
			elmBars[j].addEventListener('click', (event)=> {
				const elmTitle = document.getElementById('title');
				const elmInfTestId = document.getElementById('infTestId');
				const elmInfTestIdValue = elmInfTestId.getElementsByClassName('value')[0];
				const elmInfScore = document.getElementById('infScore');
				const elmInfScoreIdValue = elmInfScore.getElementsByClassName('value')[0];
				const elmInfTimestamp = document.getElementById('infTimestamp');
				const elmInfTimestampValue = elmInfTimestamp.getElementsByClassName('value')[0];
				const elmBtnReview = document.getElementById('btnReview');
				event.stopPropagation();
				elmMain.disableBars();
				elmBars[j].classList.add('selected');
				elmInfTestId.dataset['testId'] = elmBars[j].id;
				elmInfTestIdValue.innerText = 'T'+elmBars[j].id;
				elmInfScoreIdValue.innerText = elmBars[j].style.height;
				elmInfTimestampValue.innerText = elmBars[j].dataset['timestamp'];
				elmBtnReview.classList.remove('hidden');
			});
		}
	}

	
	const elmBtnReview = document.getElementById('btnReview');
	elmBtnReview.addEventListener('click', (event)=> {
		event.preventDefault();
		event.stopPropagation();
		elmMain.showMessage('Are you sure you want to review the test?', ()=> {
			const elmInfTestId = document.getElementById('infTestId');
			const elmMessage = document.getElementById('message');
			elmMessage.click();
			const query = new URLSearchParams({
				'testId': elmInfTestId.dataset['testId'] ,
			});
			window.open('test.php?'+query.toString(), '_self');
		});
	});
});

window.addEventListener('resize', ()=> {
	const elmMain = document.getElementById('main');
	elmMain.setDummies();
});