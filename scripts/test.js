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
	elmMain.setCounts = () => {
		const elmPhrases = document.getElementsByClassName('phrase');
		for (let i = 0; i < _items.length; i++) {
			const elmInf = document.getElementById('inf'+ucfirst(_items[i]));
			if (elmInf !== null)
				elmInf.dataset['count'] = 0;
		}
		for (let i = 0; i < elmPhrases.length; i++) {
			const elmParts = elmPhrases[i].getElementsByClassName('part');
			for (let j = 0; j < elmParts.length; j++) {
				if (elmParts[j].classList.contains('pronunciation'))
					continue;
				const elmContent = elmParts[j].getElementsByClassName('content')[0];
				const elmBoolBox = elmContent.getElementsByClassName('boolBox')[0];
				if (!elmBoolBox.classList.contains('true'))
					continue;
				for (let k = 0; k < _items.length; k++) {
					if (elmParts[j].classList.contains(_items[k])) {
						const elmInf = document.getElementById('inf'+ucfirst(_items[k]));
						elmInf.dataset['count'] = parseInt(elmInf.dataset['count']) + 1;
						break;
					}
				}
			}
		}
		for (let i = 0; i < _items.length; i++) {
			const elmInf = document.getElementById('inf'+ucfirst(_items[i]));
			if (elmInf !== null) {
				const elmInfValue = elmInf.getElementsByClassName('value')[0];
				elmInfValue.innerText = elmInf.dataset['count'];
			}
		}
	}
	elmMain.setTime = (initialize=false) => {
		const elmInfTime = document.getElementById('infTime');
		const elmInfTimeValue = elmInfTime.getElementsByClassName('value')[0];
		const timeSet = parseFloat(elmInfTime.dataset['timeSet']);
		const timeTaken = parseFloat(elmInfTime.dataset['timeTaken']);
		if (initialize) {
			const minutesSet = Math.floor(timeSet/60);
			const secondsSet = timeSet % 60;
			elmInfTime.timeSet = minutesSet.toString().padStart(2, '0')+':'+secondsSet.toString().padStart(2, '0');
		}
		const minutesTaken = Math.floor(timeTaken/60);
		const secondsTaken = timeTaken % 60;
		if (timeTaken < timeSet) {
			if (timeSet - timeTaken < 30)
				elmInfTime.classList.add('warning');
			if (timeSet - timeTaken < 10)
				elmInfTime.classList.add('error');
			elmInfTime.dataset['timeTaken'] = timeTaken + 1;
			elmInfTimeValue.innerText = minutesTaken.toString().padStart(2, '0')+':'+secondsTaken.toString().padStart(2, '0')+'/'+elmInfTime.timeSet;
		} else {
			const elmPhrases = document.getElementsByClassName('phrase');
			for (let i = 0; i < elmPhrases.length; i++) {
				const elmParts = elmPhrases[i].getElementsByClassName('part');
				for (let j = 0; j < elmParts.length; j++) {
					if (elmParts[j].classList.contains('pronunciation'))
						continue;
					const elmContent = elmParts[j].getElementsByClassName('content')[0];
					const elmInput = elmContent.getElementsByClassName('input')[0];
					elmInput.disabled = true;
				}
			}
			clearInterval(elmInfTime.timer);
			elmInfTime.dataset['timeTaken'] = timeSet;
			elmInfTimeValue.innerText = elmInfTime.timeSet+'/'+elmInfTime.timeSet;
		}
		
	};
	
	
	const elmEnvironment = elmMain.getElementsByClassName('environment')[0];
	elmEnvironment.thresholdTemporal = 200; // milliseconds
	elmEnvironment.thresholdSpatial = 50; // pixels
	elmEnvironment.addEventListener('mousedown', (event)=> {
		elmEnvironment.mouseStartTemporal = new Date().getTime();
		elmEnvironment.mouseStartSpatial = event.clientX;
	});
	elmEnvironment.addEventListener('mouseup', (event)=> {
		const diffTemporal = new Date().getTime() - elmEnvironment.mouseStartTemporal;
		const diffSpatial = event.clientX - elmEnvironment.mouseStartSpatial;
		if (diffTemporal < elmEnvironment.thresholdTemporal && Math.abs(diffSpatial) > elmEnvironment.thresholdSpatial) {
			if (diffSpatial < 0) {
				const elmBtnNext = document.getElementById('btnNext');
				elmBtnNext.click();
			} else {
				const elmBtnBack = document.getElementById('btnBack');
				elmBtnBack.click();
			}
		}
	});
	elmEnvironment.addEventListener('touchstart', (event)=> {
		const touch = event.touches[0];
		elmEnvironment.touchStartTemporal = new Date().getTime();
		elmEnvironment.touchStartSpatial = touch.clientX;
	});
	elmEnvironment.addEventListener('touchend', (event)=> {
		const touch = event.changedTouches[0];
		const diffTemporal = new Date().getTime() - elmEnvironment.touchStartTemporal;
		const diffSpatial = touch.clientX - elmEnvironment.touchStartSpatial;
		if (diffTemporal < elmEnvironment.thresholdTemporal && Math.abs(diffSpatial) > elmEnvironment.thresholdSpatial) {
			if (diffSpatial < 0) {
				const elmBtnNext = document.getElementById('btnNext');
				elmBtnNext.click();
			} else {
				const elmBtnBack = document.getElementById('btnBack');
				elmBtnBack.click();
			}
		}
	});
	elmMain.setTime(true);
	 
	
	const elmInfTime = document.getElementById('infTime');
	if (!elmInfTime.classList.contains('locked')) {
		elmInfTime.timer = setInterval(()=> {
			elmMain.setTime();
		}, 1000);
	}
	
	const elmPronunciations = document.getElementsByClassName('pronunciation');
	for (let i = 0; i < elmPronunciations.length; i++) {
		elmPronunciations[i].addEventListener('click', () => {
			const utterance = new SpeechSynthesisUtterance();
			utterance.text = elmPronunciations[i].dataset['phrase'];
			speechSynthesis.cancel();
			speechSynthesis.speak(utterance);
		});
		elmPronunciations[i].addEventListener('mousedown', (event)=> {
			event.stopPropagation();
		});
		elmPronunciations[i].addEventListener('mouseup', (event)=> {
			event.stopPropagation();
		});
		elmPronunciations[i].addEventListener('touchstart', (event)=> {
			event.stopPropagation();
		});
		elmPronunciations[i].addEventListener('touchend', (event)=> {
			event.stopPropagation();
		});
	}
	
	
	const elmInputs = document.getElementsByClassName('input');
	for (let i = 0; i < elmInputs.length; i++) {
		elmInputs[i].addEventListener('mousedown', (event)=> {
			event.stopPropagation();
		});
		elmInputs[i].addEventListener('mouseup', (event)=> {
			event.stopPropagation();
		});
		elmInputs[i].addEventListener('touchstart', (event)=> {
			event.stopPropagation();
		});
		elmInputs[i].addEventListener('touchend', (event)=> {
			event.stopPropagation();
		});
	}
	
	
	const elmBoolBoxes = document.getElementsByClassName('boolBox');
	for (let i = 0; i < elmBoolBoxes.length; i++) {
		elmBoolBoxSpans = elmBoolBoxes[i].getElementsByTagName('span');
		elmBoolBoxSpans[0].addEventListener('click', ()=> {
			elmBoolBoxes[i].classList.add('true');
			elmMain.setCounts();
		});
		elmBoolBoxSpans[1].addEventListener('click', ()=> {
			elmBoolBoxes[i].classList.remove('true');
			elmMain.setCounts();
		});
	}
	
	
	const elmSelectors = document.getElementsByClassName('selector');
	for (let i = 0; i < elmSelectors.length; i++) {
		elmSelectors[i].addEventListener('click', ()=> {
			const elmInfPhraseId = document.getElementById('infPhraseId');
			const elmInfPhraseIdValue = elmInfPhraseId.getElementsByClassName('value')[0];
			const elmEnvironment = document.getElementsByClassName('environment')[0];
			const elmPhrases = elmEnvironment.getElementsByClassName('phrase');
			const elmPronunciation = elmPhrases[i].getElementsByClassName('pronunciation')[0];
			const elmBtnBack = document.getElementById('btnBack');
			const elmBtnNext = document.getElementById('btnNext');
			elmInfPhraseId.dataset['phraseId'] = elmPhrases[i].id;
			elmInfPhraseIdValue.innerText = 'P'+elmPhrases[i].id;
			for (let j = 0; j < elmSelectors.length; j++) {
				elmSelectors[j].classList.remove('selected');
				elmPhrases[j].classList.add('hidden');
			}
			elmSelectors[i].classList.add('selected');
			elmPhrases[i].classList.remove('hidden');
			elmBtnBack.classList.remove('hidden');
			elmBtnNext.classList.remove('hidden');
			if (i === 0)
				elmBtnBack.classList.add('hidden');
			if (i === elmSelectors.length - 1)
				elmBtnNext.classList.add('hidden');
			elmPronunciation.dispatchEvent(new Event('click'));
		});
	}
	elmSelectors[0].click();
	
	
	const elmBtnCancel = document.getElementById('btnCancel');
	elmBtnCancel.addEventListener('click', (event)=> {
		event.preventDefault();
		elmMain.showMessage('Are you sure you want to cancel the test?', ()=> {
			window.open('index.php', '_self');
		});
	});
	
	const elmBtnBack = document.getElementById('btnBack');
	elmBtnBack.addEventListener('click', ()=> {
		let index;
		for (let i = 0; i < elmSelectors.length; i++) {
			if (elmSelectors[i].classList.contains('selected')) {
				index = i;
				break;
			}
		}
		if (0 < index)
			elmSelectors[index-1].click();
	});
	
	const elmBtnNext = document.getElementById('btnNext');
	elmBtnNext.addEventListener('click', ()=> {
		let index;
		for (let i = 0; i < elmSelectors.length; i++) {
			if (elmSelectors[i].classList.contains('selected')) {
				index = i;
				break;
			}
		}
		if (index < elmSelectors.length - 1)
			elmSelectors[index+1].click();
	});
	
	const elmBtnEvaluate = document.getElementById('btnEvaluate');
	elmBtnEvaluate.addEventListener('click', (event)=> {
		event.preventDefault();
		elmMain.showMessage('Are you sure you want to evaluate the test?', ()=> {
			const elmPhrases = document.getElementsByClassName('phrase');
			const elmBtnFinish = document.getElementById('btnFinish');
			const elmMessage = document.getElementById('message');
			for (let i = 0; i < elmPhrases.length; i++) {
				const elmParts = elmPhrases[i].getElementsByClassName('part');
				for (let j = 0; j < elmParts.length; j++) {
					if (elmParts[j].classList.contains('pronunciation')) {
						const elmReader = elmParts[j].getElementsByClassName('reader')[0];
						const elmSpan = elmReader.getElementsByTagName('span')[0];
						elmSpan.classList.remove('hidden');
						continue;
					}
					const elmContent = elmParts[j].getElementsByClassName('content')[0];
					const elmInput = elmContent.getElementsByClassName('input')[0];
					const elmAnswer = elmContent.getElementsByClassName('answer')[0];
					const elmBoolBox = elmContent.getElementsByClassName('boolBox')[0];
					elmInput.disabled = true;
					elmAnswer.classList.remove('hidden');
					elmBoolBox.classList.remove('hidden');
					if (elmParts[j].classList.contains('spell')) {
						const input = elmInput.value.replace(/[^\w]/gi, '').toLowerCase();
						const answer = elmAnswer.innerText.replace(/[^\w]/gi, '').toLowerCase();
						if (input === answer)
							elmBoolBox.classList.add('true');
					}
				}
			}
			clearInterval(elmInfTime.timer);
			elmMain.setCounts();
			elmBtnEvaluate.classList.add('hidden');
			elmBtnFinish.classList.remove('hidden');
			elmMessage.click();
		});
	});
	
	const elmBtnFinish = document.getElementById('btnFinish');
	elmBtnFinish.addEventListener('click', (event)=> {
		event.preventDefault();
		elmMain.showMessage('Are you sure you want to finish the evaluation?', ()=> {
			const elmBtnYes = document.getElementById('btnYes');
			if (elmBtnYes.classList.contains('locked'))
				return;
			const elmTitle = document.getElementById('title');
			const elmInfTotal = document.getElementById('infTotal');
			const elmInfTime = document.getElementById('infTime');
			const elmPhrases = document.getElementsByClassName('phrase');
			const elmMessage = document.getElementById('message');
			const dataTest = {
				'timestamp': elmTitle.dataset['timestamp'],
				'total': elmInfTotal.dataset['total'],
				'timeSet': elmInfTime.dataset['timeSet'],
				'timeTaken': elmInfTime.dataset['timeTaken']
			};
			const dataPhrases = {};
			const dataOut = {
				'dataTest': dataTest,
				'dataPhrases': dataPhrases
			}
			for (let i = 0; i < _items.length; i++) {
				const elmInf = document.getElementById('inf'+ucfirst(_items[i]));
				if (elmInf !== null)
					dataTest['totalCorrect'+ucfirst(_items[i])] = elmInf.dataset['count'];
			}
			for (let i = 0; i < elmPhrases.length; i++) {
				const elmParts = elmPhrases[i].getElementsByClassName('part');
				const dataPhrase = {};
				for (let j = 0; j < elmParts.length; j++) {
					if (elmParts[j].classList.contains('pronunciation'))
						continue;
					const elmContent = elmParts[j].getElementsByClassName('content')[0];
					const elmInput = elmContent.getElementsByClassName('input')[0];
					const elmBoolBox = elmContent.getElementsByClassName('boolBox')[0];
					for (let k = 0; k < _items.length; k++) {
						if (elmParts[j].classList.contains(_items[k])) {
							dataPhrase[_items[k]+'Given'] = elmInput.value;
							dataPhrase['is'+ucfirst(_items[k])+'Correct'] = +elmBoolBox.classList.contains('true');
						}
					}
				}
				dataPhrases[elmPhrases[i].id] = dataPhrase;
			}
			elmBtnYes.classList.add('locked');
			postData(dataOut).then((dataIn)=> {
				if (dataIn !== 'success') 
					return;
				const elmBoolBoxes = document.getElementsByClassName('boolBox');
				const elmBtnCancel = document.getElementById('btnCancel');
				const elmBtnNew = document.getElementById('btnNew');
				const elmBtnStat = document.getElementById('btnStat');
				for (let i = 0; i < elmBoolBoxes.length; i++)
					elmBoolBoxes[i].classList.add('disabled');
				elmBtnCancel.classList.add('hidden');
				elmBtnFinish.classList.add('hidden');
				elmBtnNew.classList.remove('hidden');
				elmBtnStat.classList.remove('hidden');
				elmMessage.click();
			});
		});
	});
});