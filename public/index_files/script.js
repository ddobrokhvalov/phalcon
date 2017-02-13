/*
 * If you have any questions or comments about this script or if you'd like to report a bug please contact ask.dev at gmail.com
 */
 var App = {
	doc: $(document),
	win: $(window),
	con: $('.container')
};


(function($) {
	
	var modLoad = function() {
		
		var loadList = [];
		
		if (Modernizr.touch) {
			loadList.push(BASE_PATH+'js/jquery.touchswipe.js');
		}
		
		if (loadList.length) {
			
			Modernizr.load([{
				both: loadList,
				complete: function(){
					
					pageInit();
					
				}
			}]);
		
		} else {
			
			pageInit();
			
		}
		
	},
	
	pageInit = function() {
		
		new Navigation();
		
		if ($('.js-intro').length) new Intro();
		
		var isProjectsPage = $('.js-project-list-group').length;
		if (isProjectsPage) new ProjectList();
		
		new ProjectPopupControl();
		
		App.doc.trigger('route:check', {clear: !isProjectsPage});
		
		setTimeout(asyncScriptLoad, 1000);
		
	},
	
	asyncScriptLoad = function() {
	
		var cache = $.ajaxSettings.cache; 
		$.ajaxSettings.cache = true;
		
		$.getScript('http://connect.facebook.net/en_US/all.js', function() {      
			FB.init({status: false, cookie: true, xfbml: true});    
		});
		
		$.ajaxSettings.cache = cache;
		
	}
	
	App.win.load(function(){
	//App.doc.ready(function(){
		modLoad();	
	});
	
	App.win.resize(function() {
		if(this.resizeTO) clearTimeout(this.resizeTO);
		this.resizeTO = setTimeout(function() {
			$(this).trigger('resizeEnd');
		}, 500);
	});
	
	
	// Usage: $(['img1.jpg','img2.jpg']).preloadImages(function(){ ... });
	$.fn.preloadImages = function(callback) {
		var checklist = this.toArray();
		this.each(function() {
			$('<img>').load(function() {
				checklist.remove($(this).attr('src'));
				if (checklist.length == 0) callback();
			}).attr({ src: this });
		});
	};
	
	Array.prototype.remove = function(element) {
		for (var i = 0; i < this.length; i++) {
			if (this[i] == element) this.splice(i,1);
		}
	};
	

})(jQuery);


/*    INTRO    */

function Intro() {
	
	var context,
		imgSrc,
		lightSkin,
		
		navItemsElem,
		navItemsLen,
		navIndex,
		navActive,
		navLock,
		
		contentItemsElem;
	
	var create = function() {
		
		context = $('.js-intro');
		content = $('.js-intro-content');
		
		navActive = false;
		
		navItemsElem = $('.js-intro-nav-item');
		navItemsLen = navItemsElem.length;
		
		$('.js-intro-nav').css({marginTop: '-'+( ((navItemsLen*40 + (navItemsLen-1)*20)/2) +.5|0 )+'px'});
		
		contentItemsElem = $('.js-intro-content-item');
		
		videoBlocks = $('.js-intro-video-block');
		
		bind();
		render();
	},
	
	render = function() {	
		navIndex = 0;
		navLock = true;
		
		var elem = navItemsElem.eq(navIndex);
		
		elem.addClass('__active');
		imgSrc = elem.data('src');
		
		contentItemsElem.each(function() {
			if ($(this).children('.js-case').length) {
				$(this).addClass('__scroll');
				var cssHref = $(this).children('.js-case').data('csshref');
				if (typeof(cssHref) != 'undefined') {
					var cssLink = $('<link rel="stylesheet" type="text/css" href="'+cssHref+'">');
					$('HEAD').append(cssLink);
				}
			}
		});
		
		contentItemsElem.eq(navIndex).addClass('__active');
		
		lightSkin = (elem.data('lightskin') && elem.data('lightskin') == 1);//false;
		change();
		
	},
	
	reset = function() {
		context.css({backgroundImage: (imgSrc == 'none')?'none':'url('+imgSrc+')'});
		
		setTimeout(activate, 10);
	},
	
	deactivate = function() {
		context.removeClass('__active');
	},
	
	activate = function() {
		if (navItemsLen > 1 && !navActive) {
			navActive = true;
			$('.js-intro-nav').addClass('__active');
		}
		
		context.addClass('__active');
		//contentItemsElem.eq(navIndex).addClass('__active');
		
		navLock = false;
	},
	
	bind = function() {
		navItemsElem.on('click', navItemsClick);
		App.doc.on('intro:navigate', navigate);
		
		contentItemsElem.on('click', 'A', checkLink);
		
		//
		videoBlocks.on('click', embedVideo);
		App.doc.on('popup:onshow', onshowPopup);
	},
	
	navItemsClick = function() {
		if (navLock) return false;
		if ($(this).hasClass('__active')) return false;
		
		navItemsSelect($(this));
		
		App.doc.trigger('route:goto', $(this).data('nav'));
		
		//setTimeout(change, 500);
		change();
	},
	
	navigate = function(e, data) {
		
		if (data.nav == '') return;
		
		var elem = navItemsElem.filter('[data-nav="'+data.nav+'"]');
		if (!elem.length) {
			App.doc.trigger('route:goto', ''); 
			return;
		}
		
		navItemsSelect(elem);
		
		change();
		
	},
	
	navItemsSelect = function(elem) {
		
		navItemsElem.eq(navIndex).removeClass('__active');
		contentItemsElem.eq(navIndex).removeClass('__active');
		if (contentItemsElem.eq(navIndex).hasClass('__scroll')) contentItemsElem.eq(navIndex).scrollTop(0);
		
		elem.addClass('__active');
		navIndex = elem.index();
		
		navLock = true;
		deactivate();
		
		lightSkin = (elem.data('lightskin') && elem.data('lightskin') == 1);
		imgSrc = elem.data('src');
		
	},
	
	change = function() {
		App.con.css({backgroundColor: (lightSkin?'#000':'#fff')});
		App.doc.trigger('intro:changed', {index: navIndex, lightskin: lightSkin});
		
		contentItemsElem.eq(navIndex).addClass('__active');
		
		if (!imgSrc || imgSrc == 'none') reset();
		else $([imgSrc]).preloadImages(reset);
		
		clearVideo();
	},
	
	checkLink = function() {
		// /https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/
		
		var url = $(this).attr('href'),
		reg = /https?:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/,
		match = url.match(reg);
		
		if (match) {
			var popup = $('.js-project-popup[data-popup="video"]');
			if (popup.length) {
				$('.js-embed-video', popup).data('src', match[2]);
				App.doc.trigger('popup:show', {name: 'video', noroute: true});
				return false;
			}
		}
		
	},
	
	embedVideo = function() {
		if ($(this).hasClass('__active')) return;
		
		$(this).html('<iframe src="http://player.vimeo.com/video/'+$(this).data('src')+'?title=0&amp;byline=0&amp;portrait=0&amp;color=e34f3f&amp;autoplay=1" width="580" height="327" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
		$(this).addClass('__active');
	},
	
	onshowPopup = function() {
		clearVideo();
	},
	
	clearVideo = function() {
		videoBlocks.each(function() {
			$(this).removeClass('__active').html('');
		});
	};
	
	create();
}


/*    NAVIGATION    */

function Navigation() {
	
	var context, elements,
	
		lightSkin, popupOpened;
	
	var create = function() {
	
		context = $('.js-main-nav');
		elements = {
			logo: $('.js-main-logo'),
			header: $('.js-main-header'),
            topLogo: $('.js-top-logo')
		};
		
		lightSkin = context.hasClass('__lightskin');
		popupOpened = false;
		
		bindAll();
		
	},
	
	bindAll = function() {
		
		elements.logo.on('click', logoClick);
		
		context.on('click', '.js-main-nav-item', navClick);
        elements.topLogo.on('click', navClick);

		App.doc.on('popup:onshow', function(e, data) {
			if (data.lightskin) {
				//if (!lightSkin) 
				context.addClass('__lightskin');
			} else {
				context.removeClass('__lightskin');
			}
			
			$('.js-main-nav-item').removeClass('__active');
			$('.js-main-nav-item[data-popup="'+data.name+'"]').addClass('__active');
			
			elements.logo.addClass('__clickable');
			popupOpened = true;
		});
		
		App.doc.on('popup:onhide', function(e, data) {
			if (data.lightskin) {
				if (!lightSkin) context.removeClass('__lightskin');
			} else {
				if (lightSkin) context.addClass('__lightskin');
			}
			
			$('.js-main-nav-item[data-popup="'+data.name+'"]').removeClass('__active');
			
			elements.logo.removeClass('__clickable');
			popupOpened = false;
		});
		
		App.doc.on('popup:colorchange', function(e, data) {
			if (data.lightskin) {
				context.addClass('__lightskin');
			} else {
				context.removeClass('__lightskin');
			}
		});
		
		App.doc.on('intro:changed', function(e, data) {
			if (!popupOpened) {
				if (data.lightskin) {
					context.addClass('__lightskin');
					elements.header.addClass('__lightskin');
				} else {
					context.removeClass('__lightskin');
					elements.header.removeClass('__lightskin');
				}
			}
			lightSkin = data.lightskin;
		});
		
		App.doc.on('route:check', checkRoute);
		
		App.doc.on('route:goto', routeGo); 
	},
	
	navClick = function() {
		
		if ($(this).hasClass('__active')) return false;
		
		if ($(this).data('popup')) {
			App.doc.trigger('popup:show', {name: $(this).data('popup')});
		}
	},
	
	logoClick = function() {
		if (elements.logo.hasClass('__clickable')) App.doc.trigger('popup:hide');
	}
	
	checkRoute = function(e, data) {
		if (document.location.hash != '' && document.location.hash != '#') {
			if (document.location.hash.substring(1,6) == 'intro') {
				App.doc.trigger('intro:navigate', {nav: document.location.hash.substring(1)});
			} else {
				App.doc.trigger('popup:show', {name: document.location.hash.substr(1), clear: data.clear});
			}
		}
	},
	
	routeGo = function(e, path) {
		
		if (path == '') {
			if (Modernizr.history) {
				history.pushState('', document.title, window.location.pathname);
			} else {
				document.location.hash = '';
			}
		} else {
			document.location.hash = '#'+path;
		}
		
	}
	
	
	create();
	
}


/*    PROJECT LIST    */

function ProjectList() {
	
	var listElem,
		listBaseConElem, listGhostConElem,
		listSideConElem, listBgConElem,
		
		windowHeight,
		listWidth, listConHeight,
		listBaseWidth, listBaseMaxOffset,
		listPosPercent,
		swipeData,
		sideCheckLeft,
		
		listShiftLock,
		listAnimationDuration,
		
		listScrolling,
		listScrollbarElem, listScrollerElem,
		listScrollbarWidth, listScrollerWidth,
		
		itemsSeq,
		itemsArray,
		
		shakeInterval, deviceMotion,
		deviceX, deviceY, deviceZ;
	
	var create = function() {
	
		listElem = $('.js-project-list-group');
		listBaseConElem = $('.js-project-list-base-container');
		listGhostConElem = $('.js-project-list-ghost-container');
		listSideConElem = $('.js-project-list-side-container');
		listBgConElem = $('.js-project-list-bg-container');
		
		listScrollbarElem = $('.js-project-list-scrollbar');
		listScrollerElem = $('.js-project-list-scroller');
		listScrollbarWidth = listScrollbarElem.width();
		
		windowHeight = App.win.height();
		listWidth = App.con.width();
		listConHeight = listBaseConElem.height();
		
		loadList();
		
		//showSlot();
		
		//changeBounds();
		
	},
	
	
	showSlot = function() {
		listAnimationDuration = 0;
		$('.js-slot-block').addClass('__visible');
		setTimeout(openSlot, 500);
	},
	
	openSlot = function() {
		
		$('.js-slot-block').addClass('__rotate');
		
		listPosPercent = 0;
		shiftList(0, 0);
		
		setTimeout(prepareList, 500);
		
	},
	
	
	
	loadList = function() {
		
		$.getJSON(ProjectsPreviewUrl, function(previewData){
			itemsSeq = [],
			itemsArray = {};
			
			var template = $('#tmpl-project-preview').html(),
				data,
				itemElem,
				
				saved = [],
				delta = ((windowHeight-60-listConHeight)/2)+.5|0,
				minPos = 0, maxPos = 0,
				minLeft = 9999, maxLeft = 0;
				
			for (var pos in previewData) {
				data = previewData[pos],
				itemElem = $(tmpl(template, data));
				
				itemElem.appendTo(listBaseConElem);
				
				var top = parseInt(data.top),
					left = parseInt(data.left) + (parseInt(data.col)-1)*550, // Column Width=550
					shiftleft = left+listWidth,
					width = itemElem.outerWidth(),
					height = itemElem.outerHeight(),
					css;
					
				/*if (Modernizr.csstransforms && Modernizr.csstransitions) {
					css = {transform: 'translateX('+shiftleft+'px)'};
				} else {
					css = {marginLeft: shiftleft+'px'};
				}*/
				
				css = {};
				css.top = top+'px';
				css.left = left+'px';
				
				itemElem
					.css(css)
					.data('pos', pos);
					
				if (data.satellite) {
					if (top < -delta || (top+height) > listConHeight+delta) itemElem.appendTo(listGhostConElem); 
				}
				if (data.background) {
					itemElem.appendTo(listBgConElem); 
				}
				
				if (left < minLeft) {
					minLeft = left;
					minPos = pos;
				}
				if (left > maxLeft) {
					maxLeft = left;
					maxPos = pos;
				}
				
				itemsArray[pos] = {
					elem:			itemElem,
					top:			top,
					left:			left,
					width:			width,
					height:			height,
					isClickable:	itemElem.hasClass('__clickable'),
					isSatellite:	itemElem.hasClass('__satellite'),
					isBackground:	itemElem.hasClass('__background'),
					isGhost:		false,
					isSide:			false,
					isFirst:		false,
					isLast:			false
				};
				
				if (data.popup && data.popup.length) {
					if ($.inArray(data.popup, saved) == -1) {
						saved.push(data.popup);
						itemsSeq.push({
							popup: data.popup//,
							//white: data.white,
							//content: data.content
						});
					}
				}
			}
			
			itemsArray[minPos].isFirst = true;
			itemsArray[maxPos].isLast = true;
			
			listBaseWidth = itemsArray[maxPos].left+50;
			
			//
			loadPopups();
		});
		
	},
	
	
	
	prepareList = function() {
	
		listElem.addClass('__visible');
		
		listScrolling = false;
		listAnimationDuration = 500;
		sideCheckLeft = 50;
		
		
		var percent = (((90-listWidth)/listBaseMaxOffset)*100);
		shiftList(0, percent);
		
		setTimeout(activateList, 500);
		
	},
	
	activateList = function() {
	
		listElem.addClass('__active');
		
		listShiftLock = false;
		listAnimationDuration = 500;
		
		setTimeout(function() {
			//$('.js-scroll-hint').addClass('__active');
			listScrollbarElem.addClass('__active');
		}, 500);
		
		setTimeout(bindList, 500);
		
	},
	
	
	bindList = function() {
		
		//stopScrollLeft = stopScrollRight = false;
		
		if (Modernizr.touch) {
			
			listElem.swipe({
				click:function(event, target) {
					var item = false;
					if ($(target).hasClass('js-project-item')) item = $(target);
					else if ($(target).parents('.js-project-item').length) item = $(target).parents('.js-project-item').eq(0);
					
					if (item && item.hasClass('__clickable')) item.trigger('click');
				},
				swipeStatus:function(event, phase, direction, distance, duration, fingers) {
					var delta = 0;
					if (phase == 'start') {
						swipeData = {
							direction: direction,
							distance: 0,
							lastdelta: 0
						};
						setScrolling();
					}
					if (phase == 'move') {
						if (direction == swipeData.direction) {
							delta = distance-swipeData.distance;
							swipeData.distance = distance;
						} else {
							delta = 0;
							swipeData = {
								direction: direction,
								distance: 0,
								lastdelta: 0
							};
						}
						
						if (delta != 0) {
							swipeData.lastdelta = delta;
							if (direction == 'left' || direction == 'right') {
								var percent = delta/listBaseWidth*100;
								if (percent != 0) shiftList((direction == 'left')?percent:-percent, 0);
								
								//$('.__accent').html(direction+': '+(((direction == 'left')?-delta:delta)/listBaseWidth*100));
							}
						}
					}
					if (phase == 'end' || phase == 'cancel') {
						unsetScrolling();
						
						if (swipeData.lastdelta != 0) {
							if (swipeData.direction == 'left' || swipeData.direction == 'right') {
								var percent = swipeData.lastdelta/listBaseWidth*100;
								if (percent != 0) shiftList((swipeData.direction == 'left')?percent:-percent, 0);
							}
						}
					}
				},
				threshold:50,
				fingers:1
			});
			
			listScrollbarElem.swipe({
				swipeStatus:function(event, phase, direction, distance, duration, fingers) {
					var delta = 0;
					if (phase == 'start') {
						swipeData = {
							direction: direction,
							distance: 0
						};
						setScrolling();
						listScrollbarElem.addClass('__hover');
					}
					if (phase == 'move') {
						if (direction == swipeData.direction) {
							delta = distance-swipeData.distance;
							swipeData.distance = distance;
						} else {
							delta = 0;
							swipeData = {
								direction: direction,
								distance: 0
							};
						}
						
						if (delta != 0) {
							if (direction == 'left' || direction == 'right') {
								var percent = delta/(listScrollbarWidth-listScrollerWidth)*100;
								if (percent != 0) shiftList((direction == 'left')?-percent:percent, 0);
							}
						}
					}
					if (phase == 'end' || phase == 'cancel') {
						unsetScrolling();
						listScrollbarElem.removeClass('__hover');
					}
				},
				threshold:50,
				fingers:1
			});
			
			bindShake();
		
			App.doc.on('popup:onshow', function() {
				unsetShake();
			});
			App.doc.on('popup:onhide', function() {
				setShake();
			});
		
	
		
		} else {
			
			listElem.bind('mousewheel DOMMouseScroll', wheelList);
			App.doc.on('keydown', keyPressList);
			
			listScrollerElem.draggable({containment:'parent', axis:'X', drag: dragScroller});
			
			listScrollbarElem.on('mousedown', setScrolling);
			listScrollbarElem.on('mouseup', unsetScrolling);
		}
		
		
		App.win.bind('resizeEnd', changeBounds);
		
	},
	
	
	
	getItemsSeq = function(name) {
		
		var cur, prev, next;
		for (var i=0;i<itemsSeq.length;i++) {
			if (itemsSeq[i].popup == name) {
				cur = i;
				i = itemsSeq.length;
			}
		}
		
		prev = cur-1;
		if (prev < 0) prev = itemsSeq.length-1;
		next = cur+1;
		if (next > itemsSeq.length-1) next = 0;
		
		return [prev, next];
		
	},
	
	loadPopups = function() {
		
		$.getJSON(ProjectsPopupUrl, function(popupsData){
			var template = $('#tmpl-project-popup').html(),
				titleData = {},
				data, seq;
			for (var i in popupsData) {
				data = popupsData[i];
				titleData[data.name] = {
					title: data.title,
					subtitle: data.subtitle
				};
			}
			for (var i in popupsData) {
				data = popupsData[i];
				seq = getItemsSeq(data.name);
				
				data.prev = {};
				data.prev.popup = itemsSeq[seq[0]].popup;
				//data.prev.preview = {white: itemsSeq[seq[0]].white, content: itemsSeq[seq[0]].content};
				data.prev.title = titleData[data.prev.popup].title
				data.prev.subtitle = titleData[data.prev.popup].subtitle;
				
				data.next = {};
				data.next.popup = itemsSeq[seq[1]].popup;
				//data.next.preview = {white: itemsSeq[seq[1]].white, content: itemsSeq[seq[1]].content};
				data.next.title = titleData[data.next.popup].title;
				data.next.subtitle = titleData[data.next.popup].subtitle;
				
				App.con.append($(tmpl(template, data)));
			}
			
			bindPopups();
		});
		
	},
	
	bindPopups = function() {
		
		listElem.on('click', '.js-project-item.__clickable', showPopup);
		
		listElem.on('mouseenter', '.js-project-item', hoverItem);
		listElem.on('mouseleave', '.js-project-item', outItem);
		
		changeBounds();
		showSlot();
		
		App.doc.trigger('route:check', {clear: true});
		
	},
	
	
	
	setScrolling = function() {
		if (listScrolling) return;
		listScrolling = true;
		listElem.addClass('__scrolling');
		listScrollbarElem.addClass('__scrolling');
		
		unsetShake();
	},
	
	unsetScrolling = function() {
		if (!listScrolling) return;
		listScrolling = false;
		listElem.removeClass('__scrolling');
		listScrollbarElem.removeClass('__scrolling');
		
		setShake();
	},
	
	changeBounds = function() {
		windowHeight = App.win.height();
		
		listWidth = App.con.width();
		
		listBaseConElem.css('width', (listWidth + listBaseWidth)+'px');
		listGhostConElem.css('width', (listWidth + listBaseWidth)+'px');
		listBgConElem.css('width', (listWidth + listBaseWidth)+'px');
		listBaseMaxOffset = -(listWidth + listBaseWidth);
		
		if (listBaseWidth > listWidth) {
			listScrollerWidth = (listWidth*listScrollbarWidth)/listBaseWidth + .5|0;
			//console.log(listWidth+' '+listScrollbarWidth+' '+listBaseWidth);
			listScrollerElem.css('width', listScrollerWidth);
			listScrollbarElem.removeClass('__disabled');
		} else {
			listScrollbarElem.addClass('__disabled');
		}
		
		resetGhostItems();
	},
	
	setItemsShiftLock = function() {
		listShiftLock = true;
		setTimeout(function() { listShiftLock = false; }, 250);
	},
	
	dragScroller = function(e,ui) {
		shiftList(0, (ui.position.left*100/(listScrollbarWidth-listScrollerWidth)));
	},
	
	keyPressList = function(e){
		if (e.keyCode == 37 || e.keyCode == 39) {
			
			if (listShiftLock) return false;
			setItemsShiftLock();
			
			unsetScrolling();
			
			shiftList((e.keyCode == 37)?2.5:-2.5, 0);
			return false;
		
		}
	},
	
	wheelList = function(e) {
		
		if (listShiftLock) return false;
		setItemsShiftLock();
		
		setScrolling();
		
		var oe = e.originalEvent,
			delta = 0;
		
		if (oe.wheelDelta) delta = -oe.wheelDelta;
		if (oe.detail) delta = oe.detail * 40;
		
		shiftList(((delta > 0)?3:-3), 0);
		unsetScrolling();
		
	},
	
	shiftList = function(delta, pos) {
		
		var prevLeft = (listWidth+(listBaseMaxOffset*listPosPercent/100)+.5|0);
		
		if (delta != 0) {
			listPosPercent += delta;
			if (listPosPercent < 0) listPosPercent = 0;
			if (listPosPercent > 100) listPosPercent = 100;
		} else {
			listPosPercent = pos;
		}
		
		var left = (listWidth+(listBaseMaxOffset*listPosPercent/100)+.5|0);
		
		listBaseConElem.shift(left, (listScrolling)?0:listAnimationDuration);
		listGhostConElem.shift(left, (listScrolling)?0:listAnimationDuration);
		listBgConElem.shift(left, (listScrolling)?0:listAnimationDuration);
		
		resetSideItems(left, (prevLeft - left));
		
		var left = (listPosPercent*(listScrollbarWidth-listScrollerWidth)/100)+.5|0;
		
		listScrollerElem.css('left', left+'px');
		
	},
	
	
	
	setItemOpacity = function(pos) {
		return function() {
			if (itemsArray[pos].isSide/* || itemsArray[pos].isGhost*/) {
				itemsArray[pos].elem.addClass('__ghost');
			}
		}
	},
	
	unsetItemOpacity = function(pos) {
		return function() {
			if (!itemsArray[pos].isSide/* && !itemsArray[pos].isGhost*/) {
				itemsArray[pos].elem.removeClass('__ghost');
			}
		}
	},
	

	
	resetGhostItems = function() {
		
		var delta = ((windowHeight-60-listConHeight)/2)+.5|0;
		
		for (var pos in itemsArray) {
			if (itemsArray[pos].isSatellite) {
				var top = itemsArray[pos].top;//,
					//left = itemsArray[pos].realleft;
				
				if (top < -delta || (top+itemsArray[pos].height) > listConHeight+delta) {
					if (!itemsArray[pos].isGhost) {
						itemsArray[pos].isGhost = true;
						itemsArray[pos].elem.appendTo(listGhostConElem); 
						//setTimeout(setItemOpacity(pos), 10);
					}
				} else {
					if (itemsArray[pos].isGhost) {
						itemsArray[pos].isGhost = false;
						if (!itemsArray[pos].isSide) {
							itemsArray[pos].elem.appendTo(listBaseConElem);
							//setTimeout(unsetItemOpacity(pos), 10); 
						}
					}
				}
			}
		}
		
	},
	
	setSideItem = function(pos) {
		return function() {
			itemsArray[pos].isSide = true;
			itemsArray[pos].elem.css('left', '0px').appendTo(listSideConElem);
			setTimeout(setItemOpacity(pos), 10); 
		}
	},
	
	unsetSideItem = function(pos) {
		return function() {
			itemsArray[pos].isSide = false;
			itemsArray[pos].elem.css('left', itemsArray[pos].left+'px').appendTo((itemsArray[pos].isGhost)?listGhostConElem:listBaseConElem);
			setTimeout(unsetItemOpacity(pos), 10); 			
		}
	},
	
	resetSideItems = function(left, delta) {
		//console.log('LEFT = '+left+', D = '+delta);
		for (var pos in itemsArray) {
			if (!itemsArray[pos].isBackground) {
				var itemLeft = itemsArray[pos].left + left;
				if (!itemsArray[pos].isSide) {
					if (Modernizr.cssanimations) {
						if (itemLeft < sideCheckLeft) {
							
							//CHECKER
							if (delta < 0) console.error('Delta negative1 ('+delta+') !!!');
							//
							
							var time = (listScrolling)?0:((delta + itemLeft - sideCheckLeft)/(delta/listAnimationDuration))+.5|0;
							setTimeout(setSideItem(pos), time); 
						}
					} else {
						if (itemLeft < 0) {
							
							//CHECKER
							if (delta < 0) console.error('Delta negative2!!!');
							//
							
							var time = (listScrolling)?0:((delta + itemLeft)/(delta/listAnimationDuration))+.5|0;
							setTimeout(setSideItem(pos), time); 
						}
					}
				} else {
					if (itemLeft > 0) {
					
						//CHECKER
						if (delta > 0) console.error('Delta positive ('+delta+') !!!');
						//
						
						var time = (listScrolling)?0:((delta + itemLeft)/(delta/listAnimationDuration))+.5|0;
						setTimeout(unsetSideItem(pos), time); 
					}
				}
			}
		}
	},
	
	showPopup = function(e) {
		
		var item = $(this);
		if (item.data('popup')) {
			App.doc.trigger('popup:show', {name: $(this).data('popup')}); 
		}
		
	},
	
	hoverItem = function() {
		
		var item = $(this);
		if (item.data('popup')) {
			$('.js-project-item[data-popup="'+item.data('popup')+'"]').addClass('__hover');
		}
		
	},
	
	outItem = function() {
		
		var item = $(this);
		if (item.data('popup')) {
			$('.js-project-item[data-popup="'+item.data('popup')+'"]').removeClass('__hover');
		}
		
	},
	
	detectShake = function() {
	
		if (listShiftLock) return false;
		setItemsShiftLock();
		
		var angle;
		if (Math.abs(window.orientation) === 90) {
			//landscape
			angle = getRoll(deviceX, deviceY, deviceZ);
			if (window.orientation > 0) angle = angle*-1;
		} else {
			//portrait
			angle = getPitch(deviceX, deviceY, deviceZ);
			if (window.orientation > 0) angle = angle*-1;
		}
		
		if (Math.abs(angle) > 1) shiftList(-0.5*angle, 0);
		
		function getPitch(x, y, z) {
			var pitch = Math.atan (x / Math.sqrt(y*y + z*z));
			return pitch = (pitch * 180) / Math.PI;
		}
		function getRoll(x, y, z) {
			var roll = Math.atan (y / Math.sqrt(x*x + z*z));
			return (roll * 180) / Math.PI;
		}
		
		//$('.__accent').html(deviceX+' '+deviceY+' '+deviceZ);
		
	},
	
	bindShake = function() {
	
		if (typeof window.DeviceMotionEvent != 'undefined') {
			
			deviceMotion = true;
			
			window.addEventListener('devicemotion', function (e) {
				deviceX = e.accelerationIncludingGravity.x;
				deviceY = e.accelerationIncludingGravity.y;
				deviceZ = e.accelerationIncludingGravity.z;
			}, false);

			shakeInterval = setInterval(detectShake, 150);
			
		}
		
	},
	
	unsetShake = function() {
		if (deviceMotion) clearInterval(shakeInterval);
	}
	setShake = function() {
		if (deviceMotion) shakeInterval = setInterval(detectShake, 150);
	};
	
	create();
	
	
	$.fn.shift = function(left, duration) {
		if (Modernizr.csstransforms && Modernizr.csstransitions) {
			if (Modernizr.csstransforms3d) $(this).css({transform: 'translate3d('+left+'px, 0, 0)'});
			else $(this).css({transform: 'translateX('+left+'px)'});
		} else {
			if (duration > 0) $(this).stop().animate({marginLeft: left+'px'}, {duration: duration});
			else $(this).css({marginLeft: left+'px'});
		}
	}
	
}



/*    PROJECT POPUP    */

function ProjectPopupControl() {

	var popupsObj, popupOpened;
	
	var create = function() {
		
		popupsObj = {};
		popupOpened = '';
		
		bind();
	}
	
	bind = function() {
	
		App.doc.on('popup:show', showPopup);
		App.doc.on('popup:hide', hidePopup);
		App.doc.on('popup:onshow', onshowPopup);
		App.doc.on('popup:onhide', onhidePopup);
		
	},
	
	checkPopup = function(name) {
		
		if (popupsObj[name]) {
			return true;
		}
		
		var elem = $('.js-project-popup[data-popup="'+name+'"]');
		if (elem.length) {
			popupsObj[name] = new ProjectPopupItem(name);
			
			return true;
		}
		
		return false;
	},
	
	showPopup = function(e, data) {
		
		if (!checkPopup(data.name)) {
			if (data.clear) App.doc.trigger('route:goto', ''); 
			return;
		}
		
		if (data.name == popupOpened) return;
		
		if (popupOpened != '') popupsObj[popupOpened].hideDeferred();
		
		//console.log(data.name);
		popupOpened = data.name;
		popupsObj[data.name].show(data);
		
	},
	
	hidePopup = function(e) {
		
		if (popupOpened != '') {
			popupsObj[popupOpened].hide();
			
			App.doc.trigger('route:goto', ''); 
		}
		
	},
	
	onshowPopup = function() {
	
	},
	onhidePopup = function() {
		popupOpened = '';
	};
	
	
	create();
}


/*    PERSON LIST    */

function PersonList(selector) {

	var context,
		elements;
	
	var create = function() {
		
		context = $('#'+selector);
		
		elements = {};
		
		elements.items = $('.js-person-item', context);
		
		bind();
	}
	
	bind = function() {
		
		//elements.items.on('click', clickItem);
		
		if (Modernizr.touch) {
			elements.items.on('click', clickItem);
		} else {
			elements.items.on('mouseenter', clickItem);
			elements.items.on('mouseleave', outItem);
		}
		
		App.doc.on('popup:keep', hideAll);
		App.doc.on('popup:onshow', onshowPopup);
		App.doc.on('popup:onhide', onhidePopup);
		
	},
	
	clickItem = function(e) {
		
		elements.items.removeClass('__active');
		$(this).addClass('__active');
		
		e.stopPropagation();
	},
	outItem = function(e) {
		
		$(this).removeClass('__active');
		
		e.stopPropagation();
	},
	
	hideAll = function() {
		elements.items.removeClass('__active');
	},
	
	onshowPopup = function() {
	
	},
	onhidePopup = function() {
		
	};
	
	
	create();
}


/*    VIDEO BLOCK    */

function VideoBlock(selector) {

	var context,
		elements,
		isEmbed;
	
	var create = function() {
		
		context = $('#'+selector);
		
		elements = {};
		
		elements.items = $('.js-video-item', context);
		elements.player = $('.js-video-player', context);
		elements.embed = $('.js-video-player-embed', context);
		
		isEmbed = false;
		
		bind();
	}
	
	bind = function() {
	
		elements.items.on('click', clickVideoItem);
		
		App.doc.on('popup:onshow', onshowPopup);
		App.doc.on('popup:onhide', onhidePopup);
		
	},
	
	clickVideoItem = function() {
		//if (isEmbed) {
		if ($(this).hasClass('__active') && isEmbed) {
			elements.player.removeClass('__active');
			elements.embed.html('');
			elements.items.removeClass('__active');
			isEmbed = false;
		} else {
			elements.items.removeClass('__active');
			elements.embed.html('<iframe src="http://player.vimeo.com/video/'+$(this).data('src')+'?title=0&amp;byline=0&amp;portrait=0&amp;color=a6ba00&amp;autoplay=1" width="640" height="360" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
			$(this).addClass('__active');
			elements.player.addClass('__active');
			isEmbed = true;
		}
		
		setTimeout(function() {
			App.doc.trigger('popup:resized');
		}, (Modernizr.csstransitions)?300:1)
	},
	
	onshowPopup = function() {
	
	},
	onhidePopup = function() {
		if (isEmbed) {
			elements.player.removeClass('__active');
			elements.embed.html('');
			elements.items.removeClass('__active');
			isEmbed = false;
		}
	};
	
	
	create();
}


/*    AUDIO BLOCK    */

function AudioBlock(selector) {

	var context,
		player;
	
	var create = function() {
		
		context = $('#'+selector);
		
		player = document.createElement('audio');
		var source= document.createElement('source');
		if (player.canPlayType('audio/mpeg;')) {
			source.type= 'audio/mpeg';
			source.src = context.data('sourcemp3');
		} else {
			source.type= 'audio/ogg';
			source.src=  context.data('sourceogg');
		}
		player.appendChild(source);
		
		$('BODY').append($(player));
		
		bind();
	}
	
	bind = function() {
	
		context.on('click', click);
		//player.addEventListener('loadeddata', loaded, false);
		loaded();
		
		$(player).on('ended', ended);
		
		App.doc.on('popup:onshow', onshowPopup);
		App.doc.on('popup:onhide', onhidePopup);
		
	},
	
	click = function() {
		if (!player.paused) {
			context.removeClass('__playing');
			player.pause();
		} else {
			context.addClass('__playing');
			player.play();
		}
	},
	
	loaded = function() {
		context.addClass('__ready');
	},
	
	ended = function() {
		if (player.paused) {
			context.removeClass('__playing');
			player.pause();
		}
	},
	
	onshowPopup = function() {
	
	},
	onhidePopup = function() {
		if (!player.paused) {
			context.removeClass('__playing');
			player.pause();
		}
	};
	
	
	create();
}



/*    PROJECT POPUP ONE    */

function ProjectPopupItem(name) {

	var context,
		
		elements,
		
		baseLightSkin,
		curLightSkin,
		isStatic,
		
		containerHeight, wrapperHeight, 
		scrollbarHeight, scrollerHeight,
		headerHeight,
		
		swipeData,
		
		scrollPosPercent,
		scrollDisabled,
		isScrolling,
		
		subNavTop, hasSubNav, isSubNavFixed,
		
		scrollColorFrames, hasColors;
	
	var create = function() {
	
		context = $('.js-project-popup[data-popup="'+name+'"]');
		elements = {};
		
		elements.wrapper = $('.js-project-popup-wrapper', context);
		elements.container = $('.js-project-popup-container', context);
		
		elements.border = $('.js-project-popup-border', context);
		
		elements.scrollbar = $('.js-project-popup-scrollbar', context);
		elements.scroller = $('.js-project-popup-scroller', context);
		
		
		scrollPosPercent = 0;
		scrollDisabled = false;
		isScrolling = false;
		
		headerHeight = 100;
		
		
		baseLightSkin = context.hasClass('__lightskin');
		isStatic = context.hasClass('__static');
		isChangeRoute = true;
		
		curLightSkin = baseLightSkin;
		
		//initSubNav();
		//initScrollColorFrames();
		//initContent();
		
		//changeBounds();
		bindAll();
		
		App.win.bind('resizeEnd', changeBounds);
	},
		
	bindAll = function() {
		
		/*if (Modernizr.touch) {
			bindSwipe();
		} else {
			elements.scroller.draggable({containment:'parent', axis:'Y', drag: dragScroller});
			context.bind('mousewheel DOMMouseScroll', wheelContainer);
			
			elements.scrollbar.on('mousedown', setScrolling);
			elements.scrollbar.on('mouseup', unsetScrolling);
		}*/
		
		elements.barScroll = elements.wrapper.baron({
			root: elements.wrapper,
			scroller: elements.wrapper,
			bar: elements.scroller,
			track: elements.scrollbar,
			barOnCls: 'baron'
		});
		elements.wrapper.on('scroll', function() { updateContainer(); });
		
		context.on('click', '.js-project-popup-border', keepPopup);
		context.on('click', '.js-project-popup-nav', resetPopup);
		context.on('click', '.js-project-popup-close', closePopup);
		context.on('click', hidePopup);
		
		//if (hasSubNav) elements.subnavItems.on('click', clickSubNav);
		
		App.doc.on('popup:resized', changeBounds);
	},
	
	changeBounds = function() {
		
		elements.container.removeClass('__middlecentered');
		
		//scrollbarHeight = elements.scrollbar.height();
		wrapperHeight = context.height();// - 60;
		containerHeight = elements.container.outerHeight();
		
		elements.wrapper.css('height', wrapperHeight);
		
		//scrollerHeight = (wrapperHeight*scrollbarHeight/containerHeight)+.5|0; 
		
		//elements.scroller.css({height: scrollerHeight+'px'});
		
		/*if (scrollbarHeight - scrollerHeight <= 0) {
			scrollDisabled = true;
			scrollPosPercent = 0;
			elements.scrollbar.addClass('__disabled');
		} else {
			scrollDisabled = false;
			elements.scrollbar.removeClass('__disabled');
		}
		
		if (scrollDisabled) {
			if (containerHeight < wrapperHeight-headerHeight && isStatic) elements.container.addClass('__middlecentered');
		} else {
			var top = parseInt(elements.scroller.css('top'));
			if (top > scrollbarHeight - scrollerHeight) {
				scrollContainer(0, 100);
			}
		}*/
		
		if (containerHeight < wrapperHeight-headerHeight && isStatic) {
			elements.container.addClass('__middlecentered');
			elements.scrollbar.addClass('__disabled');
		} else {
			elements.scrollbar.removeClass('__disabled');
			elements.barScroll.update();
			updateContainer();
		}
		
		if (hasSubNav && !isSubNavFixed) subNavTop = elements.subnavRel.offset().top;
		
		resizeScrollColorFrames();
	},
	
	/*dragScroller = function(e,ui) {
	
		if (scrollDisabled) return false;
		
		scrollContainer(0, (ui.position.top/(scrollbarHeight - scrollerHeight))*100);
		
	},
	
	wheelContainer = function (e) {
	
		if (scrollDisabled) return false;
		
		setScrolling();
		var oe = e.originalEvent,
			delta = 0;
		
		if (oe.wheelDelta) delta = -oe.wheelDelta;
		if (oe.detail) delta = oe.detail * 40;
		
		var percent = (delta/(containerHeight-wrapperHeight))*100;
		scrollContainer(percent, 0);
		setTimeout(unsetScrolling, 1);
		
		return false;
		
	},
	
	scrollContainer = function(delta, percent) {
		
		if (delta != 0) {
			scrollPosPercent += delta;
			if (scrollPosPercent < 0) scrollPosPercent = 0;
			if (scrollPosPercent > 100) scrollPosPercent = 100;
		} else {
			scrollPosPercent = percent;
		}
		
		var top = ((containerHeight-wrapperHeight)*scrollPosPercent/100)+.5|0;
		
		if (Modernizr.csstransforms && Modernizr.csstransitions) {
			elements.container.css({transform: 'translateY('+-top+'px)'});
		} else {
			elements.container.css({marginTop: -top+'px'});
		}
		
		resetColor(top);
		resetSubNav(top);
		
		var top = ((scrollbarHeight - scrollerHeight)*scrollPosPercent/100)+.5|0;
		elements.scroller.css('top', top+'px');
		
	},*/
	
	updateContainer = function() {
		var top = elements.wrapper.scrollTop();
		resetColor(top);
		resetSubNav(top);
	},
	
	showPopup = function(data) {
		
		context.find('.js-embed-video').each(embedVideo);
		
		context.addClass('__foreground __active __visible');
		
		changeBounds();
		
		//elements.scroller.css('top', '0px');
		//scrollContainer(0, 0);
		//unsetScrolling();
		elements.wrapper.scrollTop(0);
		elements.barScroll.update();
		updateContainer();
		
		if (data.noroute) {
			isChangeRoute = false;
		} else {
			isChangeRoute = true;
			App.doc.trigger('route:goto', name); 
		}
		
		App.doc.trigger('popup:onshow', {name: name, lightskin: baseLightSkin});
		
		if ($('.js-project-popup-loader', context).length) loadContent();
		
	},
	
	loadContent = function() {
		elements.loader = $('.js-project-popup-loader', context);
		elements.loadContainer = $('<DIV/>');
		elements.loader.after(elements.loadContainer);
		elements.loadContainer.load(elements.loader.data('url'), function(data) {
			elements.loader.remove();
			
			initSubNav();
			initScrollColorFrames();
			initContent();
			
			changeBounds();
		});
	},
	
	initContent = function() {
		if ($('.js-video-block', context).length) {
			$('.js-video-block', context).each(function(i) {
				var selector = 'js-video-block-'+name+i;
				$(this).attr({id: selector});
				new VideoBlock(selector);
			});
		}
		
		if (Modernizr.audio && $('.js-audio-block', context).length) {
			$('.js-audio-block', context).each(function(i) {
				var selector = 'js-audio-block-'+name+i;
				$(this).attr({id: selector});
				new AudioBlock(selector);
			});
		}
		
		if ($('.js-person-list', context).length) {
			$('.js-person-list', context).each(function(i) {
				var selector = 'js-person-list-'+name+i;
				$(this).attr({id: selector});
				new PersonList(selector);
			});
		}
	},
	
	resetPopup = function() {
	
		if ($(this).data('popup')) {
			App.doc.trigger('popup:show', {name: $(this).data('popup')}); 
		}
		
	},
	
	keepPopup = function(e) {
		
		if (context.hasClass('__transparent')) {
			closePopup();
			return;
		}
		
		e.stopPropagation();
		
		App.doc.trigger('popup:keep');
	},
	
	closePopup = function(e) {
	
		hidePopup();
		e.stopPropagation();
		
		if (isChangeRoute) App.doc.trigger('route:goto', ''); 
	
	},
	
	hidePopup = function() {
		
		context.find('.js-embed-video').each(clearVideo);
		
		if (Modernizr.csstransitions) {
			
			context.removeClass('__foreground __visible');
			var _context = context;
			setTimeout(function() {
				if (!_context.hasClass('__foreground')) _context.removeClass('__active');
			}, 250);
			
		} else {
			
			context.removeClass('__foreground __active __visible');
			
		}
		
		
		afterHidePopup();
	},
	
	hidePopupDeferred = function() {
		
		if (Modernizr.csstransitions) {
			
			context.removeClass('__foreground');
			var _context = context;
			setTimeout(function() {
				if (!_context.hasClass('__foreground')) _context.removeClass('__active __visible');
			}, 250);
			
		} else {
			
			context.removeClass('__foreground __active __visible');
			
		}
		
		afterHidePopup();
		
	},
	
	afterHidePopup = function() {
		
		if (baseLightSkin) {
			if (!curLightSkin) context.addClass('__lightskin');
		} else {
			if (curLightSkin) context.removeClass('__lightskin');
		}
		
		//setScrolling();
		clearSubNav();
		//elements.scroller.css('top', '0px');
		//scrollContainer(0, 0);
		
		App.doc.trigger('popup:onhide', {name: name, lightskin: curLightSkin});
		
	},
	
	embedVideo = function() {
		if ($(this).data('src')) {
			$(this).html('<iframe src="http://player.vimeo.com/video/'+$(this).data('src')+'?title=0&amp;byline=0&amp;portrait=0&amp;color=a6ba00" width="800" height="450" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
		}
	},
	
	clearVideo = function() {
		$(this).html('');
	},
	
	initScrollColorFrames = function() {
		
		hasColors = false;
		scrollColorFrames = {};
		
		if (context.find('.js-bgcolor-text').length) {
			context.find('.js-bgcolor-text').each(function(i) {
				scrollColorFrames[i] = {
					elem: $(this),
					offset: $(this).position().top,
					bgcolor: $(this).data('bgcolor'),
					lightskin: $(this).data('lightskin')
				};
			});
			hasColors = true;
		}
		
	},
	
	resizeScrollColorFrames = function() {
		
		if (hasColors) {
			for (var i in scrollColorFrames) {
				scrollColorFrames[i].offset = scrollColorFrames[i].elem.position().top;
			};
			
			//resetColor(((containerHeight-wrapperHeight)*scrollPosPercent/100)+.5|0);
			resetColor(elements.wrapper.scrollTop());
		}
		
	},
	
	resetColor = function(top) {
		
		if (hasColors) {
			var bgColor = '', newLightSkin = curLightSkin;
			
			top += App.con.height()/2 +.5|0;
			
			for (var i in scrollColorFrames) {
				if (scrollColorFrames[i].offset < top) {
					bgColor = scrollColorFrames[i].bgcolor;
					newLightSkin = scrollColorFrames[i].lightskin;
				}
			}
			
			if (bgColor != '') elements.border.css({backgroundColor: bgColor});
			
			if (curLightSkin != newLightSkin) {
				App.doc.trigger('popup:colorchange', {lightskin: newLightSkin});
				
				if (newLightSkin) {
					if (!curLightSkin) context.addClass('__lightskin');
				} else {
					if (curLightSkin) context.removeClass('__lightskin');
				}
				
				curLightSkin = newLightSkin;
			}
		}
		
	},
	
	initSubNav = function() {
		
		elements.subnav = $('.js-project-popup-subnav', context);
		hasSubNav = elements.subnav.length;
		isSubNavFixed = false;
		
		if (hasSubNav) {
			elements.subnavRel = elements.subnav.parent();
			subNavTop = elements.subnavRel.offset().top;
			elements.subnavItems = $('.js-project-popup-subnav-item', elements.subnav);
			elements.subnavAnchors = $('.js-project-popup-subnav-anchor', context);
			
			elements.subnavItems.on('click', clickSubNav);
		}
		
	},
	
	resetSubNav = function(top) {
		
		if (!hasSubNav) return;
		
		if (subNavTop-15 < top) {
			isSubNavFixed = true;
			elements.subnav.addClass('__fixed');
			elements.subnav.appendTo(elements.border);
		} else {
			isSubNavFixed = false;
			elements.subnav.removeClass('__fixed');
			elements.subnav.appendTo(elements.subnavRel);
			//subNavTop = elements.subnav.offset().top;
		}
	},
	
	clearSubNav = function() {
		
		if (hasSubNav) {
			isSubNavFixed = false;
			elements.subnav.removeClass('__fixed');
			elements.subnav.appendTo(elements.subnavRel);
		}
		
	},
	
	clickSubNav = function() {
		
		var anchor = elements.subnavAnchors.filter('[data-name="'+$(this).data('name')+'"]');
		if (anchor.length) {
			//scrollContainer(0, ((anchor.position().top-90)/((containerHeight-wrapperHeight)))*100);
			elements.wrapper.animate({scrollTop: anchor.position().top-90});
		}
		
	}/*,
	
	setScrolling = function() {
		if (isScrolling) return;
		isScrolling = true;
		context.addClass('__scrolling');
	},
	
	unsetScrolling = function() {
		if (!isScrolling) return;
		isScrolling = false;
		context.removeClass('__scrolling');
	},
	
	bindSwipe = function() {
		elements.wrapper.swipe({
			click:function(event, target) {
				var item = false;
				
				if ($(target).hasClass('js-project-popup-nav')) item = $(target);
				else if ($(target).parents('.js-project-popup-nav').length) item = $(target).parents('.js-project-popup-nav').eq(0);
				
				else if ($(target).hasClass('js-project-item')) item = $(target);
				else if ($(target).parents('.js-project-item').length) item = $(target).parents('.js-project-item').eq(0);
				
				if (item) item.trigger('click');
			},
			swipeStatus:function(event, phase, direction, distance, duration, fingers) {
				//phase : 'start', 'move', 'end', 'cancel'
				//direction : 'left', 'right', 'up', 'down'
				var delta;
				if (phase == 'start') {
					swipeData = {
						direction: direction,
						distance: 0
					};
				}
				if (phase == 'move') {
					if (direction == swipeData.direction) {
						delta = distance-swipeData.distance;
						swipeData.distance = distance;
					} else {
						delta = 0;
						swipeData = {
							direction: direction,
							distance: 0
						};
					}
					if (!scrollDisabled && delta != 0) {
						if (direction == 'up' || direction == 'down') {
							var percent = (delta/(containerHeight-wrapperHeight))*100;
							scrollContainer((direction == 'up')?percent:-percent, 0);
						}
					}
				}
			},
			threshold:50,
			fingers:1
		});
		
		elements.scrollbar.swipe({
			swipeStatus:function(event, phase, direction, distance, duration, fingers) {
				var delta = 0;
				if (phase == 'start') {
					setScrolling();
					swipeData = {
						direction: direction,
						distance: 0
					};
					elements.scrollbar.addClass('__hover');
				}
				if (phase == 'move') {
					if (direction == swipeData.direction) {
						delta = distance-swipeData.distance;
						swipeData.distance = distance;
					} else {
						delta = 0;
						swipeData = {
							direction: direction,
							distance: 0
						};
					}
					
					if (delta != 0) {
						if (direction == 'up' || direction == 'down') {
							var percent = delta/(scrollbarHeight-scrollerHeight)*100;
							if (percent != 0) scrollContainer((direction == 'up')?-percent:percent, 0);
						}
					}
				}
				if (phase == 'end' || phase == 'cancel') {
					unsetScrolling();
					elements.scrollbar.removeClass('__hover');
				}
			},
			threshold:50,
			fingers:1
		});
	}*/;
		
	this.show = function(data) {
		showPopup(data);
	};
	this.hide = function() {
		hidePopup();
	};
	this.hideDeferred = function() {
		hidePopupDeferred();
	};
	
	create();
	
}
