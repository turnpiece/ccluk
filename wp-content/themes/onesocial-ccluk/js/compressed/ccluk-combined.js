/* Modernizr 2.8.3 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-fontface-backgroundsize-borderimage-borderradius-boxshadow-flexbox-flexboxlegacy-hsla-multiplebgs-opacity-rgba-textshadow-cssanimations-csscolumns-generatedcontent-cssgradients-cssreflections-csstransforms-csstransforms3d-csstransitions-geolocation-inlinesvg-smil-svg-svgclippaths-touch-shiv-cssclasses-teststyles-testprop-testallprops-prefixes-domprefixes-load
 */
;window.Modernizr=function(a,b,c){function B(a){j.cssText=a}function C(a,b){return B(n.join(a+";")+(b||""))}function D(a,b){return typeof a===b}function E(a,b){return!!~(""+a).indexOf(b)}function F(a,b){for(var d in a){var e=a[d];if(!E(e,"-")&&j[e]!==c)return b=="pfx"?e:!0}return!1}function G(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:D(f,"function")?f.bind(d||b):f}return!1}function H(a,b,c){var d=a.charAt(0).toUpperCase()+a.slice(1),e=(a+" "+p.join(d+" ")+d).split(" ");return D(b,"string")||D(b,"undefined")?F(e,b):(e=(a+" "+q.join(d+" ")+d).split(" "),G(e,b,c))}var d="2.8.3",e={},f=!0,g=b.documentElement,h="modernizr",i=b.createElement(h),j=i.style,k,l=":)",m={}.toString,n=" -webkit- -moz- -o- -ms- ".split(" "),o="Webkit Moz O ms",p=o.split(" "),q=o.toLowerCase().split(" "),r={svg:"http://www.w3.org/2000/svg"},s={},t={},u={},v=[],w=v.slice,x,y=function(a,c,d,e){var f,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:h+(d+1),l.appendChild(j);return f=["&#173;",'<style id="s',h,'">',a,"</style>"].join(""),l.id=h,(m?l:n).innerHTML+=f,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=g.style.overflow,g.style.overflow="hidden",g.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),g.style.overflow=k),!!i},z={}.hasOwnProperty,A;!D(z,"undefined")&&!D(z.call,"undefined")?A=function(a,b){return z.call(a,b)}:A=function(a,b){return b in a&&D(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=w.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(w.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(w.call(arguments)))};return e}),s.flexbox=function(){return H("flexWrap")},s.flexboxlegacy=function(){return H("boxDirection")},s.touch=function(){var c;return"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch?c=!0:y(["@media (",n.join("touch-enabled),("),h,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(a){c=a.offsetTop===9}),c},s.geolocation=function(){return"geolocation"in navigator},s.rgba=function(){return B("background-color:rgba(150,255,150,.5)"),E(j.backgroundColor,"rgba")},s.hsla=function(){return B("background-color:hsla(120,40%,100%,.5)"),E(j.backgroundColor,"rgba")||E(j.backgroundColor,"hsla")},s.multiplebgs=function(){return B("background:url(https://),url(https://),red url(https://)"),/(url\s*\(.*?){3}/.test(j.background)},s.backgroundsize=function(){return H("backgroundSize")},s.borderimage=function(){return H("borderImage")},s.borderradius=function(){return H("borderRadius")},s.boxshadow=function(){return H("boxShadow")},s.textshadow=function(){return b.createElement("div").style.textShadow===""},s.opacity=function(){return C("opacity:.55"),/^0.55$/.test(j.opacity)},s.cssanimations=function(){return H("animationName")},s.csscolumns=function(){return H("columnCount")},s.cssgradients=function(){var a="background-image:",b="gradient(linear,left top,right bottom,from(#9f9),to(white));",c="linear-gradient(left top,#9f9, white);";return B((a+"-webkit- ".split(" ").join(b+a)+n.join(c+a)).slice(0,-a.length)),E(j.backgroundImage,"gradient")},s.cssreflections=function(){return H("boxReflect")},s.csstransforms=function(){return!!H("transform")},s.csstransforms3d=function(){var a=!!H("perspective");return a&&"webkitPerspective"in g.style&&y("@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}",function(b,c){a=b.offsetLeft===9&&b.offsetHeight===3}),a},s.csstransitions=function(){return H("transition")},s.fontface=function(){var a;return y('@font-face {font-family:"font";src:url("https://")}',function(c,d){var e=b.getElementById("smodernizr"),f=e.sheet||e.styleSheet,g=f?f.cssRules&&f.cssRules[0]?f.cssRules[0].cssText:f.cssText||"":"";a=/src/i.test(g)&&g.indexOf(d.split(" ")[0])===0}),a},s.generatedcontent=function(){var a;return y(["#",h,"{font:0/0 a}#",h,':after{content:"',l,'";visibility:hidden;font:3px/1 a}'].join(""),function(b){a=b.offsetHeight>=3}),a},s.svg=function(){return!!b.createElementNS&&!!b.createElementNS(r.svg,"svg").createSVGRect},s.inlinesvg=function(){var a=b.createElement("div");return a.innerHTML="<svg/>",(a.firstChild&&a.firstChild.namespaceURI)==r.svg},s.smil=function(){return!!b.createElementNS&&/SVGAnimate/.test(m.call(b.createElementNS(r.svg,"animate")))},s.svgclippaths=function(){return!!b.createElementNS&&/SVGClipPath/.test(m.call(b.createElementNS(r.svg,"clipPath")))};for(var I in s)A(s,I)&&(x=I.toLowerCase(),e[x]=s[I](),v.push((e[x]?"":"no-")+x));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)A(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof f!="undefined"&&f&&(g.className+=" "+(b?"":"no-")+a),e[a]=b}return e},B(""),i=k=null,function(a,b){function l(a,b){var c=a.createElement("p"),d=a.getElementsByTagName("head")[0]||a.documentElement;return c.innerHTML="x<style>"+b+"</style>",d.insertBefore(c.lastChild,d.firstChild)}function m(){var a=s.elements;return typeof a=="string"?a.split(" "):a}function n(a){var b=j[a[h]];return b||(b={},i++,a[h]=i,j[i]=b),b}function o(a,c,d){c||(c=b);if(k)return c.createElement(a);d||(d=n(c));var g;return d.cache[a]?g=d.cache[a].cloneNode():f.test(a)?g=(d.cache[a]=d.createElem(a)).cloneNode():g=d.createElem(a),g.canHaveChildren&&!e.test(a)&&!g.tagUrn?d.frag.appendChild(g):g}function p(a,c){a||(a=b);if(k)return a.createDocumentFragment();c=c||n(a);var d=c.frag.cloneNode(),e=0,f=m(),g=f.length;for(;e<g;e++)d.createElement(f[e]);return d}function q(a,b){b.cache||(b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag()),a.createElement=function(c){return s.shivMethods?o(c,a,b):b.createElem(c)},a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+m().join().replace(/[\w\-]+/g,function(a){return b.createElem(a),b.frag.createElement(a),'c("'+a+'")'})+");return n}")(s,b.frag)}function r(a){a||(a=b);var c=n(a);return s.shivCSS&&!g&&!c.hasCSS&&(c.hasCSS=!!l(a,"article,aside,dialog,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}mark{background:#FF0;color:#000}template{display:none}")),k||q(a,c),a}var c="3.7.0",d=a.html5||{},e=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,f=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,g,h="_html5shiv",i=0,j={},k;(function(){try{var a=b.createElement("a");a.innerHTML="<xyz></xyz>",g="hidden"in a,k=a.childNodes.length==1||function(){b.createElement("a");var a=b.createDocumentFragment();return typeof a.cloneNode=="undefined"||typeof a.createDocumentFragment=="undefined"||typeof a.createElement=="undefined"}()}catch(c){g=!0,k=!0}})();var s={elements:d.elements||"abbr article aside audio bdi canvas data datalist details dialog figcaption figure footer header hgroup main mark meter nav output progress section summary template time video",version:c,shivCSS:d.shivCSS!==!1,supportsUnknownElements:k,shivMethods:d.shivMethods!==!1,type:"default",shivDocument:r,createElement:o,createDocumentFragment:p};a.html5=s,r(b)}(this,b),e._version=d,e._prefixes=n,e._domPrefixes=q,e._cssomPrefixes=p,e.testProp=function(a){return F([a])},e.testAllProps=H,e.testStyles=y,g.className=g.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(f?" js "+v.join(" "):""),e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};
/*
 * Swiper 1.9.2+ - Mobile Touch Slider
 * http://www.idangero.us/sliders/swiper/
 *
 * Copyright 2012-2013, Vladimir Kharlampidi
 * The iDangero.us
 * http://www.idangero.us/
 *
 * Licensed under GPL & MIT
 *
 * Updated on: April 19, 2013
*/
var Swiper = function (selector, params, callback) {
    /*=========================
      A little bit dirty but required part for IE8 and old FF support
      ===========================*/
    if (!window.addEventListener) {
        if (!window.Element)
            Element = function () { };
    
        Element.prototype.addEventListener = HTMLDocument.prototype.addEventListener = addEventListener = function (type, listener, useCapture) { this.attachEvent('on' + type, listener); }
        Element.prototype.removeEventListener = HTMLDocument.prototype.removeEventListener = removeEventListener = function (type, listener, useCapture) { this.detachEvent('on' + type, listener); }
    }
    
    if (document.body.__defineGetter__) {
        if (HTMLElement) {
            var element = HTMLElement.prototype;
            if (element.__defineGetter__)
                element.__defineGetter__("outerHTML", function () { return new XMLSerializer().serializeToString(this); } );
        }
    }
    
    if (!window.getComputedStyle) {
        window.getComputedStyle = function (el, pseudo) {
            this.el = el;
            this.getPropertyValue = function (prop) {
                var re = /(\-([a-z]){1})/g;
                if (prop == 'float') prop = 'styleFloat';
                if (re.test(prop)) {
                    prop = prop.replace(re, function () {
                        return arguments[2].toUpperCase();
                    });
                }
                return el.currentStyle[prop] ? el.currentStyle[prop] : null;
            }
            return this;
        }
    }
        
    /* End Of Polyfills*/
    if(!(selector.nodeType))
        if (!document.querySelectorAll||document.querySelectorAll(selector).length==0) return;
    
    function dQ(s) {
        return document.querySelectorAll(s)
    }
    var _this = this
    _this.touches = {};
    _this.positions = {
        current : 0 
    };
    _this.id = (new Date()).getTime();
    _this.container = (selector.nodeType) ? selector : dQ(selector)[0];
    _this.times = {};
    _this.isTouched = false;
    _this.realIndex = 0;
    _this.activeSlide = 0;
    _this.activeIndex = 0;
    _this.previousSlide = null;
    _this.previousIndex = null;
    _this.langDirection = window.getComputedStyle(_this.container, null).getPropertyValue('direction')
    /*=========================
      New Support Object
      ===========================*/
    _this.support = {
        touch : _this.isSupportTouch(),
        threeD : _this.isSupport3D()    
    }
    //For fallback with older versions
    _this.use3D = _this.support.threeD;
    
    
    /*=========================
      Default Parameters
      ===========================*/
    var defaults = {
        mode : 'horizontal',
        ratio : 1,
        speed : 300,
        freeMode : false,
        freeModeFluid : false,
        slidesPerSlide : 1,
        slidesPerGroup : 1,
        simulateTouch : true,
        followFinger : true,
        shortSwipes : true,
        moveStartThreshold:false,
        autoPlay:false,
        onlyExternal : false,
        createPagination : true,
        pagination : false,
        resistance : true,
        nopeek : false,
        scrollContainer : false,
        preventLinks : true,
        preventClassNoSwiping : true,
        initialSlide: 0,
        keyboardControl: false, 
        mousewheelControl : false,
        resizeEvent : 'orientationchange', //or 'resize' or 'orientationchange'
        useCSS3Transforms : true,
        //Namespace
        slideElement : 'div',
        slideClass : 'swiper-slide',
        wrapperClass : 'swiper-wrapper',
        paginationClass: 'swiper-pagination-switch' ,
        paginationActiveClass : 'swiper-active-switch' 
    }
    params = params || {};  
    for (var prop in defaults) {
        if (! (prop in params)) {
            params[prop] = defaults[prop]   
        }
    }
    _this.params = params;
    if (params.scrollContainer) {
        params.freeMode = true;
        params.freeModeFluid = true;    
    }
    var _widthFromCSS = false
    if (params.slidesPerSlide=='auto') {
        _widthFromCSS = true;
        params.slidesPerSlide = 1;
    }
    
    //Default Vars
    var wrapper, isHorizontal,
     slideSize, numOfSlides, wrapperSize, direction, isScrolling, containerSize;
    
    //Define wrapper
    for (var i = _this.container.childNodes.length - 1; i >= 0; i--) {
        
        if (_this.container.childNodes[i].className) {

            var _wrapperClasses = _this.container.childNodes[i].className.split(' ')

            for (var j = 0; j < _wrapperClasses.length; j++) {
                if (_wrapperClasses[j]===params.wrapperClass) {
                    wrapper = _this.container.childNodes[i]
                }
            };  
        }
    };

    _this.wrapper = wrapper;
    //Mode
    isHorizontal = params.mode == 'horizontal';
        
    //Define Touch Events
    _this.touchEvents = {
        touchStart : _this.support.touch || !params.simulateTouch  ? 'touchstart' : (_this.ie10 ? 'MSPointerDown' : 'mousedown'),
        touchMove : _this.support.touch || !params.simulateTouch ? 'touchmove' : (_this.ie10 ? 'MSPointerMove' : 'mousemove'),
        touchEnd : _this.support.touch || !params.simulateTouch ? 'touchend' : (_this.ie10 ? 'MSPointerUp' : 'mouseup')
    };

    /*=========================
      Slide API
      ===========================*/
    _this._extendSwiperSlide = function  (el) {
        el.append = function () {
            _this.wrapper.appendChild(el);
            _this.reInit();
            return el;
        }
        el.prepend = function () {
            _this.wrapper.insertBefore(el, _this.wrapper.firstChild)
            _this.reInit();
            return el;
        }
        el.insertAfter = function (index) {
            if(typeof index === 'undefined') return false;
            var beforeSlide = _this.slides[index+1]
            _this.wrapper.insertBefore(el, beforeSlide)
            _this.reInit();
            return el;
        }
        el.clone = function () {
            return _this._extendSwiperSlide(el.cloneNode(true))
        }
        el.remove = function () {
            _this.wrapper.removeChild(el);
            _this.reInit()
        }
        el.html = function (html) {
            if (typeof html === 'undefined') {
                return el.innerHTML
            }
            else {
                el.innerHTML = html;
                return el;
            }
        }
        el.index = function () {
            var index
            for (var i = _this.slides.length - 1; i >= 0; i--) {
                if(el==_this.slides[i]) index = i
            };
            return index;
        }
        el.isActive = function () {
            if (el.index() == _this.activeIndex) return true;
            else return false;
        }
        if (!el.swiperSlideDataStorage) el.swiperSlideDataStorage={};
        el.getData = function (name) {
            return el.swiperSlideDataStorage[name]
        }
        el.setData = function (name, value) {
            el.swiperSlideDataStorage[name] = value;
            return el;
        }
        el.data = function (name, value) {
            if (!value) {
                return el.getAttribute('data-'+name);
            }
            else {
                el.setAttribute('data-'+name,value);
                return el;
            }
        }
        return el;
    }

    //Calculate information about slides
    _this._calcSlides = function () {
        var oldNumber = _this.slides ? _this.slides.length : false;
        _this.slides = []
        for (var i = 0; i < _this.wrapper.childNodes.length; i++) {
            if (_this.wrapper.childNodes[i].className) {
                var _slideClasses = _this.wrapper.childNodes[i].className.split(' ')
                for (var j = 0; j < _slideClasses.length; j++) {
                    if(_slideClasses[j]===params.slideClass) _this.slides.push(_this.wrapper.childNodes[i])
                };
            } 
        };
        for (var i = _this.slides.length - 1; i >= 0; i--) {
            _this._extendSwiperSlide(_this.slides[i]);
        };
        if (!oldNumber) return;
        if(oldNumber!=_this.slides.length && _this.createPagination) {
            // Number of slides has been changed
            _this.createPagination();
            _this.callPlugins('numberOfSlidesChanged')
        }
        /*
        if (_this.langDirection=='rtl') {
            for (var i = 0; i < _this.slides.length; i++) {
                _this.slides[i].style.float="right"
            };
        }
        */
    }
    _this._calcSlides();

    //Create Slide
    _this.createSlide = function (html, slideClassList, el) {
        var slideClassList = slideClassList || _this.params.slideClass;
        var el = el||params.slideElement;
        var newSlide = document.createElement(el)
        newSlide.innerHTML = html||'';
        newSlide.className = slideClassList;
        return _this._extendSwiperSlide(newSlide);
    }

    //Append Slide  
    _this.appendSlide = function (html, slideClassList, el) {
        if (!html) return;
        if (html.nodeType) {
            return _this._extendSwiperSlide(html).append()
        }
        else {
            return _this.createSlide(html, slideClassList, el).append()
        }
    }
    _this.prependSlide = function (html, slideClassList, el) {
        if (!html) return;
        if (html.nodeType) {
            return _this._extendSwiperSlide(html).prepend()
        }
        else {
            return _this.createSlide(html, slideClassList, el).prepend()
        }
    }
    _this.insertSlideAfter = function (index, html, slideClassList, el) {
        if (!index) return false;
        if (html.nodeType) {
            return _this._extendSwiperSlide(html).insertAfter(index)
        }
        else {
            return _this.createSlide(html, slideClassList, el).insertAfter(index)
        }
    }
    _this.removeSlide = function (index) {
        if (_this.slides[index]) {
            _this.slides[index].remove()
            return true;
        }
        else return false;
    }
    _this.removeLastSlide = function () {
        if (_this.slides.length>0) {
            _this.slides[ (_this.slides.length-1) ].remove();
            return true
        }
        else {
            return false
        }
    }
    _this.removeAllSlides = function () {
        for (var i = _this.slides.length - 1; i >= 0; i--) {
            _this.slides[i].remove()
        };
    }
    _this.getSlide = function (index) {
        return _this.slides[index]
    }
    _this.getLastSlide = function () {
        return _this.slides[ _this.slides.length-1 ]
    }
    _this.getFirstSlide = function () {
        return _this.slides[0]
    }

    //Currently Active Slide
    _this.currentSlide = function () {
        return _this.slides[_this.activeIndex]
    }
    
    /*=========================
      Find All Plugins
      !!! Plugins API is in beta !!!
      ===========================*/
    var _plugins = [];
    for (var plugin in _this.plugins) {
        if (params[plugin]) {
            var p = _this.plugins[plugin](_this, params[plugin])
            if (p)
                _plugins.push( p )  
        }
    }
    
    _this.callPlugins = function(method, args) {
        if (!args) args = {}
        for (var i=0; i<_plugins.length; i++) {
            if (method in _plugins[i]) {
                _plugins[i][method](args);
            }

        }
        
    }

    /*=========================
      WP8 Fix
      ===========================*/
    if (_this.ie10 && !params.onlyExternal) {
        if (isHorizontal) _this.wrapper.classList.add('swiper-wp8-horizontal')  
        else _this.wrapper.classList.add('swiper-wp8-vertical') 
    }
    
    /*=========================
      Loop
      ===========================*/
    if (params.loop) {
        (function(){
            numOfSlides = _this.slides.length;
            if (_this.slides.length==0) return;
            var slideFirstHTML = '';
            var slideLastHTML = '';
            //Grab First Slides
            for (var i=0; i<params.slidesPerSlide; i++) {
                slideFirstHTML+=_this.slides[i].outerHTML
            }
            //Grab Last Slides
            for (var i=numOfSlides-params.slidesPerSlide; i<numOfSlides; i++) {
                slideLastHTML+=_this.slides[i].outerHTML
            }
            wrapper.innerHTML = slideLastHTML + wrapper.innerHTML + slideFirstHTML;
            _this._calcSlides()
            _this.callPlugins('onCreateLoop');
        })();
    }
    
    //Init Function
    var firstInit = false;
    //ReInitizize function. Good to use after dynamically changes of Swiper, like after add/remove slides
    _this.reInit = function () {
        _this.init(true)
    }
    _this.init = function(reInit) {
        var _width = window.getComputedStyle(_this.container, null).getPropertyValue('width')
        var _height = window.getComputedStyle(_this.container, null).getPropertyValue('height')
        var newWidth = parseInt(_width,10);
        var newHeight  = parseInt(_height,10);
        
        //IE8 Fixes
        if(isNaN(newWidth) || _width.indexOf('%')>0) {
            newWidth = _this.container.offsetWidth - parseInt(window.getComputedStyle(_this.container, null).getPropertyValue('padding-left'),10) - parseInt(window.getComputedStyle(_this.container, null).getPropertyValue('padding-right'),10) 
        }
        if(isNaN(newHeight) || _height.indexOf('%')>0) {
            newHeight = _this.container.offsetHeight - parseInt(window.getComputedStyle(_this.container, null).getPropertyValue('padding-top'),10) - parseInt(window.getComputedStyle(_this.container, null).getPropertyValue('padding-bottom'),10)         
        }
        if (!reInit) {
            if (_this.width==newWidth && _this.height==newHeight) return            
        }
        if (reInit || firstInit) {
            _this._calcSlides();
            if (params.pagination) {
                _this.updatePagination()
            }
        }
        _this.width  = newWidth;
        _this.height  = newHeight;
        
        var dividerVertical = isHorizontal ? 1 : params.slidesPerSlide,
            dividerHorizontal = isHorizontal ? params.slidesPerSlide : 1,
            slideWidth, slideHeight, wrapperWidth, wrapperHeight;

        numOfSlides = _this.slides.length
        if (!params.scrollContainer) {
            if (!_widthFromCSS) {
                slideWidth = _this.width/dividerHorizontal;
                slideHeight = _this.height/dividerVertical; 
            }
            else {
                slideWidth = _this.slides[0].offsetWidth;
                slideHeight = _this.slides[0].offsetHeight;
            }
            containerSize = isHorizontal ? _this.width : _this.height;
            slideSize = isHorizontal ? slideWidth : slideHeight;
            wrapperWidth = isHorizontal ? numOfSlides * slideWidth : _this.width;
            wrapperHeight = isHorizontal ? _this.height : numOfSlides*slideHeight;
            if (_widthFromCSS) {
                //Re-Calc sps for pagination
                params.slidesPerSlide = Math.round(containerSize/slideSize)
            }
        }
        else {
            //Unset dimensions in vertical scroll container mode to recalculate slides
            if (!isHorizontal) {
                wrapper.style.width='';
                wrapper.style.height='';
                _this.slides[0].style.width='';
                _this.slides[0].style.height='';
            }
            
            slideWidth = _this.slides[0].offsetWidth;
            slideHeight = _this.slides[0].offsetHeight;
            containerSize = isHorizontal ? _this.width : _this.height;
            
            slideSize = isHorizontal ? slideWidth : slideHeight;
            wrapperWidth = slideWidth;
            wrapperHeight = slideHeight;
        }

        wrapperSize = isHorizontal ? wrapperWidth : wrapperHeight;
    
        for (var i=0; i<_this.slides.length; i++ ) {
            var el = _this.slides[i];
            if (!_widthFromCSS) {
                el.style.width=slideWidth+"px"
                el.style.height=slideHeight+"px"
            }
            if (params.onSlideInitialize) {
                params.onSlideInitialize(_this, el);
            }
        }
        wrapper.style.width = wrapperWidth+"px";
        wrapper.style.height = wrapperHeight+"px";
        
        // Set Initial Slide Position   
        if(params.initialSlide > 0 && params.initialSlide < numOfSlides && !firstInit) {
            _this.realIndex = _this.activeIndex = params.initialSlide;
            
            if (params.loop) {
                _this.activeIndex = _this.realIndex-params.slidesPerSlide;
            }
            //Legacy
            _this.activeSlide = _this.activeIndex;
            //--          
            if (isHorizontal) {
                _this.positions.current = -params.initialSlide * slideWidth;
                _this.setTransform( _this.positions.current, 0, 0);
            }
            else {  
                _this.positions.current = -params.initialSlide * slideHeight;
                _this.setTransform( 0, _this.positions.current, 0);
            }
        }
        
        if (!firstInit) _this.callPlugins('onFirstInit');
        else _this.callPlugins('onInit');
        firstInit = true;
    }

    _this.init()

    
    
    //Get Max And Min Positions
    function maxPos() {
        var a = (wrapperSize - containerSize);
        if (params.loop) a = a - containerSize; 
        if (params.scrollContainer) {
            a = slideSize - containerSize;
            if (a<0) a = 0;
        }
        if (params.slidesPerSlide > _this.slides.length) a = 0;
        return a;
    }
    function minPos() {
        var a = 0;
        if (params.loop) a = containerSize;
        return a;   
    }
    
    /*=========================
      Pagination
      ===========================*/
    _this.updatePagination = function() {
        if (_this.slides.length<2) return;
        var activeSwitch = dQ(params.pagination+' .'+params.paginationActiveClass)
        if(!activeSwitch) return
        for (var i=0; i < activeSwitch.length; i++) {
            activeSwitch.item(i).className = params.paginationClass
        }
        var pagers = dQ(params.pagination+' .'+params.paginationClass).length;
        var minPagerIndex = params.loop ? _this.realIndex-params.slidesPerSlide : _this.realIndex;
        var maxPagerIndex = minPagerIndex + (params.slidesPerSlide-1);
        for (var i = minPagerIndex; i <= maxPagerIndex; i++ ) {
            var j = i;
            if (j>=pagers) j=j-pagers;
            if (j<0) j = pagers + j;
            if (j<numOfSlides) {
                dQ(params.pagination+' .'+params.paginationClass).item( j ).className = params.paginationClass+' '+params.paginationActiveClass
            }
            if (i==minPagerIndex) dQ(params.pagination+' .'+params.paginationClass).item( j ).className+=' swiper-activeslide-switch'
        }
    }
    _this.createPagination = function () {
        if (params.pagination && params.createPagination) {
            var paginationHTML = "";
            var numOfSlides = _this.slides.length;
            var numOfButtons = params.loop ? numOfSlides - params.slidesPerSlide*2 : numOfSlides;
            for (var i = 0; i < numOfButtons; i++) {
                paginationHTML += '<span class="'+params.paginationClass+'"></span>'
            }
            dQ(params.pagination)[0].innerHTML = paginationHTML
            _this.updatePagination()
            
            _this.callPlugins('onCreatePagination');
        }   
    }
    _this.createPagination();
    
    
    //Window Resize Re-init
    _this.resizeEvent = params.resizeEvent==='auto' 
        ? ('onorientationchange' in window) ? 'orientationchange' : 'resize'
        : params.resizeEvent

    _this.resizeFix = function(){
        _this.callPlugins('beforeResizeFix');
        _this.init()
        //To fix translate value
        if (!params.scrollContainer) 
            _this.swipeTo(_this.activeIndex, 0, false)
        else {
            var pos = isHorizontal ? _this.getTranslate('x') : _this.getTranslate('y')
            if (pos < -maxPos()) {
                var x = isHorizontal ? -maxPos() : 0;
                var y = isHorizontal ? 0 : -maxPos();
                _this.setTransition(0)
                _this.setTransform(x,y,0)   
            }
        }
        _this.callPlugins('afterResizeFix');
    }
    if (!params.disableAutoResize) {
        //Check for resize event
        window.addEventListener(_this.resizeEvent, _this.resizeFix, false)
    }

    /*========================================== 
        Autoplay 
    ============================================*/

    var autoPlay
    _this.startAutoPlay = function() {
        if (params.autoPlay && !params.loop) {
            autoPlay = setInterval(function(){
                var newSlide = _this.realIndex + 1
                if ( newSlide == numOfSlides) newSlide = 0 
                _this.swipeTo(newSlide) 
            }, params.autoPlay)
        }
        else if (params.autoPlay && params.loop) {
            autoPlay = setInterval(function(){
                _this.fixLoop();
                _this.swipeNext(true)
            }, params.autoPlay)
        }
        _this.callPlugins('onAutoPlayStart');
    }
    _this.stopAutoPlay = function() {
        if (autoPlay)
            clearInterval(autoPlay)
        _this.callPlugins('onAutoPlayStop');
    }
    if (params.autoPlay) {
        _this.startAutoPlay()
    }
    
    /*========================================== 
        Event Listeners 
    ============================================*/
    
    if (!_this.ie10) {
        if (_this.support.touch) {
            wrapper.addEventListener('touchstart', onTouchStart, false);
            wrapper.addEventListener('touchmove', onTouchMove, false);
            wrapper.addEventListener('touchend', onTouchEnd, false);    
        }
        if (params.simulateTouch) {
            wrapper.addEventListener('mousedown', onTouchStart, false);
            document.addEventListener('mousemove', onTouchMove, false);
            document.addEventListener('mouseup', onTouchEnd, false);
        }
    }
    else {
        wrapper.addEventListener(_this.touchEvents.touchStart, onTouchStart, false);
        document.addEventListener(_this.touchEvents.touchMove, onTouchMove, false);
        document.addEventListener(_this.touchEvents.touchEnd, onTouchEnd, false);
    }
    
    //Remove Events
    _this.destroy = function(removeResizeFix){
        removeResizeFix = removeResizeFix===false ? removeResizeFix : removeResizeFix || true
        if (removeResizeFix) {
            window.removeEventListener(_this.resizeEvent, _this.resizeFix, false);
        }

        if (_this.ie10) {
            wrapper.removeEventListener(_this.touchEvents.touchStart, onTouchStart, false);
            document.removeEventListener(_this.touchEvents.touchMove, onTouchMove, false);
            document.removeEventListener(_this.touchEvents.touchEnd, onTouchEnd, false);
        }
        else {
            if (_this.support.touch) {
                wrapper.removeEventListener('touchstart', onTouchStart, false);
                wrapper.removeEventListener('touchmove', onTouchMove, false);
                wrapper.removeEventListener('touchend', onTouchEnd, false); 
            }
            wrapper.removeEventListener('mousedown', onTouchStart, false);
            document.removeEventListener('mousemove', onTouchMove, false);
            document.removeEventListener('mouseup', onTouchEnd, false);
        }

        if (params.keyboardControl) {
            document.removeEventListener('keydown', handleKeyboardKeys, false);
        }
        if (params.mousewheelControl && _this._wheelEvent) {
            _this.container.removeEventListener(_this._wheelEvent, handleMousewheel, false);
        }
        if (params.autoPlay) {
            _this.stopAutoPlay()
        }
        _this.callPlugins('onDestroy');
    }
    /*=========================
      Prevent Links
      ===========================*/

    _this.allowLinks = true;
    if (params.preventLinks) {
        var links = _this.container.querySelectorAll('a')
        for (var i=0; i<links.length; i++) {
            links[i].addEventListener('click', preventClick, false) 
        }
    }
    function preventClick(e) {
        if (!_this.allowLinks) e.preventDefault();  
    }

    /*========================================== 
        Keyboard Control 
    ============================================*/
    if (params.keyboardControl) {
        function handleKeyboardKeys (e) {
            var kc = e.keyCode || e.charCode;
            if (isHorizontal) {
                if (kc==37 || kc==39) e.preventDefault();
                if (kc == 39) _this.swipeNext()
                if (kc == 37) _this.swipePrev()
            }
            else {
                if (kc==38 || kc==40) e.preventDefault();
                if (kc == 40) _this.swipeNext()
                if (kc == 38) _this.swipePrev()
            }
        }
        document.addEventListener('keydown',handleKeyboardKeys, false);
    }

    /*========================================== 
        Mousewheel Control. Beta! 
    ============================================*/
    // detect available wheel event
    _this._wheelEvent = false;
    
    if (params.mousewheelControl) {
        if ( document.onmousewheel !== undefined ) {
            _this._wheelEvent = "mousewheel"
        }
            try {
                WheelEvent("wheel");
                _this._wheelEvent = "wheel";
            } catch (e) {}
            if ( !_this._wheelEvent ) {
                _this._wheelEvent = "DOMMouseScroll";
            }
        function handleMousewheel (e) {
            if(e.preventDefault) e.preventDefault();
            var we = _this._wheelEvent;
            var delta;
            //Opera & IE
            if (e.detail) delta = -e.detail;
            //WebKits   
            else if (we == 'mousewheel') delta = e.wheelDelta; 
            //Old FireFox
            else if (we == 'DOMMouseScroll') delta = -e.detail;
            //New FireFox
            else if (we == 'wheel') {
                delta = Math.abs(e.deltaX)>Math.abs(e.deltaY) ? - e.deltaX : - e.deltaY;
            }
            if (!params.freeMode) {
                if(delta<0) _this.swipeNext()
                else _this.swipePrev()
            }
            else {
                //Freemode or scrollContainer:
                var currentTransform =isHorizontal ? _this.getTranslate('x') : _this.getTranslate('y')
                var x,y;
                if (isHorizontal) {
                    x = _this.getTranslate('x') + delta;
                    y = _this.getTranslate('y');
                    if (x>0) x = 0;
                    if (x<-maxPos()) x = -maxPos();
                }
                else {
                    x = _this.getTranslate('x');
                    y = _this.getTranslate('y')+delta;
                    if (y>0) y = 0;
                    if (y<-maxPos()) y = -maxPos();
                }
                _this.setTransition(0)
                _this.setTransform(x,y,0)
            }

            e.preventDefault();
            return false;
        }
        if (_this._wheelEvent) {
            _this.container.addEventListener(_this._wheelEvent, handleMousewheel, false);
        }
    }
    /*=========================
      Grab Cursor
      ===========================*/
    if (params.grabCursor) {
        _this.container.style.cursor = 'move';
        _this.container.style.cursor = 'grab';
        _this.container.style.cursor = '-moz-grab';
        _this.container.style.cursor = '-webkit-grab';
    }  
    /*=========================
      Handle Touches
      ===========================*/
    //Detect event type for devices with both touch and mouse support
    var isTouchEvent = false; 
    var allowThresholdMove; 
    function onTouchStart(event) {
        //Exit if slider is already was touched
        if (_this.isTouched || params.onlyExternal) {
            return false
        }
        if (params.preventClassNoSwiping && event.target.className.indexOf('NoSwiping') > -1) return false;
        
        //Check For Nested Swipers
        _this.isTouched = true;
        isTouchEvent = event.type=='touchstart';
        if (!isTouchEvent || event.targetTouches.length == 1 ) {
            _this.callPlugins('onTouchStartBegin');
            
            if (params.loop) _this.fixLoop();
            if(!isTouchEvent) {
                if(event.preventDefault) event.preventDefault();
                else event.returnValue = false;
            }
            var pageX = isTouchEvent ? event.targetTouches[0].pageX : (event.pageX || event.clientX)
            var pageY = isTouchEvent ? event.targetTouches[0].pageY : (event.pageY || event.clientY)
            
            //Start Touches to check the scrolling
            _this.touches.startX = _this.touches.currentX = pageX;
            _this.touches.startY = _this.touches.currentY = pageY;
            
            _this.touches.start = _this.touches.current = isHorizontal ? _this.touches.startX : _this.touches.startY ;
            
            //Set Transition Time to 0
            _this.setTransition(0)
            
            //Get Start Translate Position
            _this.positions.start = _this.positions.current = isHorizontal ? _this.getTranslate('x') : _this.getTranslate('y');

            //Set Transform
            if (isHorizontal) {
                _this.setTransform( _this.positions.start, 0, 0)
            }
            else {
                _this.setTransform( 0, _this.positions.start, 0)
            }
            
            //TouchStartTime
            var tst = new Date()
            _this.times.start = tst.getTime()
            
            //Unset Scrolling
            isScrolling = undefined;
            
            //Define Clicked Slide without additional event listeners
            if (params.onSlideClick || params.onSlideTouch) {
                ;(function () {
                    var el = _this.container;
                    var box = el.getBoundingClientRect();
                    var body = document.body;
                    var clientTop  = el.clientTop  || body.clientTop  || 0;
                    var clientLeft = el.clientLeft || body.clientLeft || 0;
                    var scrollTop  = window.pageYOffset || el.scrollTop;
                    var scrollLeft = window.pageXOffset || el.scrollLeft;
                    var x = pageX - box.left + clientLeft - scrollLeft;
                    var y = pageY - box.top - clientTop - scrollTop;
                    var touchOffset = isHorizontal ? x : y; 
                    var beforeSlides = -Math.round(_this.positions.current/slideSize)
                    var realClickedIndex = Math.floor(touchOffset/slideSize) + beforeSlides
                    var clickedIndex = realClickedIndex;
                    if (params.loop) {
                        var clickedIndex = realClickedIndex - params.slidesPerSlide;
                        if (clickedIndex<0) {
                            clickedIndex = _this.slides.length+clickedIndex-(params.slidesPerSlide*2);
                        }

                    }
                    _this.clickedSlideIndex = clickedIndex
                    _this.clickedSlide = _this.getSlide(realClickedIndex);
                    if (params.onSlideTouch) {
                        params.onSlideTouch(_this);
                        _this.callPlugins('onSlideTouch');
                    }
                })();
            }
            //Set Treshold
            if (params.moveStartThreshold>0) allowThresholdMove = false;
            //CallBack
            if (params.onTouchStart) params.onTouchStart(_this)
            _this.callPlugins('onTouchStartEnd');
            
        }
    }
    function onTouchMove(event) {
        // If slider is not touched - exit
        if (!_this.isTouched || params.onlyExternal) return;
        if (isTouchEvent && event.type=='mousemove') return;
        var pageX = isTouchEvent ? event.targetTouches[0].pageX : (event.pageX || event.clientX)
        var pageY = isTouchEvent ? event.targetTouches[0].pageY : (event.pageY || event.clientY)
        //check for scrolling
        if ( typeof isScrolling === 'undefined' && isHorizontal) {
          isScrolling = !!( isScrolling || Math.abs(pageY - _this.touches.startY) > Math.abs( pageX - _this.touches.startX ) )
        }
        if ( typeof isScrolling === 'undefined' && !isHorizontal) {
          isScrolling = !!( isScrolling || Math.abs(pageY - _this.touches.startY) < Math.abs( pageX - _this.touches.startX ) )
        }

        if (isScrolling ) {
            _this.isTouched = false;
            return
        }
        
        //Check For Nested Swipers
        if (event.assignedToSwiper) {
            _this.isTouched = false;
            return
        }
        event.assignedToSwiper = true;  
        
        //Block inner links
        if (params.preventLinks) {
            _this.allowLinks = false;   
        }
        
        //Stop AutoPlay if exist
        if (params.autoPlay) {
            _this.stopAutoPlay()
        }
        if (!isTouchEvent || event.touches.length == 1) {
            
            _this.callPlugins('onTouchMoveStart');

            if(event.preventDefault) event.preventDefault();
            else event.returnValue = false;
            
            _this.touches.current = isHorizontal ? pageX : pageY ;
            
            _this.positions.current = (_this.touches.current - _this.touches.start)*params.ratio + _this.positions.start            
            
            if (params.resistance) {
                //Resistance for Negative-Back sliding
                if(_this.positions.current > 0 && !(params.freeMode&&!params.freeModeFluid)) {
                    
                    if (params.loop) {
                        var resistance = 1;
                        if (_this.positions.current>0) _this.positions.current = 0;
                    }
                    else {
                        var resistance = (containerSize*2-_this.positions.current)/containerSize/2;
                    }
                    if (resistance < 0.5) 
                        _this.positions.current = (containerSize/2)
                    else 
                        _this.positions.current = _this.positions.current * resistance
                        
                    if (params.nopeek)
                        _this.positions.current = 0;
                    
                }
                //Resistance for After-End Sliding
                if ( (_this.positions.current) < -maxPos() && !(params.freeMode&&!params.freeModeFluid)) {
                    
                    if (params.loop) {
                        var resistance = 1;
                        var newPos = _this.positions.current
                        var stopPos = -maxPos() - containerSize
                    }
                    else {
                        
                        var diff = (_this.touches.current - _this.touches.start)*params.ratio + (maxPos()+_this.positions.start)
                        var resistance = (containerSize+diff)/(containerSize);
                        var newPos = _this.positions.current-diff*(1-resistance)/2
                        var stopPos = -maxPos() - containerSize/2;
                    }
                    
                    if (params.nopeek) {
                        newPos = _this.positions.current-diff;
						stopPos = -maxPos();
					}
                    
                    if (newPos < stopPos || resistance<=0)
                        _this.positions.current = stopPos;
                    else 
                        _this.positions.current = newPos
                }
            }
            
            //Move Slides
            if (!params.followFinger) return

            if (!params.moveStartThreshold) {
                if (isHorizontal) _this.setTransform( _this.positions.current, 0, 0)
                else _this.setTransform( 0, _this.positions.current, 0)    
            }
            else {
                if ( Math.abs(_this.touches.current - _this.touches.start)>params.moveStartThreshold || allowThresholdMove) {
                    allowThresholdMove = true;
                    if (isHorizontal) _this.setTransform( _this.positions.current, 0, 0)
                    else _this.setTransform( 0, _this.positions.current, 0)  
                }
                else {
                    _this.positions.current = _this.positions.start
                }
            }    
            
            
            if (params.freeMode) {
                _this.updateActiveSlide(_this.positions.current)
            }

            //Prevent onSlideClick Fallback if slide is moved
            if (params.onSlideClick && _this.clickedSlide) {
                _this.clickedSlide = false
            }

            //Grab Cursor
            if (params.grabCursor) {
                _this.container.style.cursor = 'move';
                _this.container.style.cursor = 'grabbing';
                _this.container.style.cursor = '-moz-grabbin';
                _this.container.style.cursor = '-webkit-grabbing';
            }  

            //Callbacks
            _this.callPlugins('onTouchMoveEnd');
            if (params.onTouchMove) params.onTouchMove(_this)

            return false
        }
    }
    function onTouchEnd(event) {
        //Check For scrolling
        if (isScrolling) _this.swipeReset();
        // If slider is not touched exit
        if ( params.onlyExternal || !_this.isTouched ) return
        _this.isTouched = false

        //Release inner links
        if (params.preventLinks) {
            setTimeout(function(){
                _this.allowLinks = true;    
            },10)
        }

        //Return Grab Cursor
        if (params.grabCursor) {
            _this.container.style.cursor = 'move';
            _this.container.style.cursor = 'grab';
            _this.container.style.cursor = '-moz-grab';
            _this.container.style.cursor = '-webkit-grab';
        } 

        //onSlideClick
        if (params.onSlideClick && _this.clickedSlide) {
            params.onSlideClick(_this);
            _this.callPlugins('onSlideClick')
        }

        //Check for Current Position
        if (!_this.positions.current && _this.positions.current!==0) {
            _this.positions.current = _this.positions.start 
        }
        
        //For case if slider touched but not moved
        if (params.followFinger) {
            if (isHorizontal) _this.setTransform( _this.positions.current, 0, 0)
            else _this.setTransform( 0, _this.positions.current, 0)
        }
        //--
        
        // TouchEndTime
        var tet = new Date()
        _this.times.end = tet.getTime();
        
        //Difference
        _this.touches.diff = _this.touches.current - _this.touches.start        
        _this.touches.abs = Math.abs(_this.touches.diff)
        
        _this.positions.diff = _this.positions.current - _this.positions.start
        _this.positions.abs = Math.abs(_this.positions.diff)
        
        var diff = _this.positions.diff ;
        var diffAbs =_this.positions.abs ;

        if(diffAbs < 5) {
            _this.swipeReset()
        }
        
        var maxPosition = wrapperSize - containerSize;
        if (params.scrollContainer) {
            maxPosition = slideSize - containerSize
        }
        
        //Prevent Negative Back Sliding
        if (_this.positions.current > 0) {
            _this.swipeReset()
            if (params.onTouchEnd) params.onTouchEnd(_this)
            _this.callPlugins('onTouchEnd');
            return
        }
        //Prevent After-End Sliding
        if (_this.positions.current < -maxPosition) {
            _this.swipeReset()
            if (params.onTouchEnd) params.onTouchEnd(_this)
            _this.callPlugins('onTouchEnd');
            return
        }
        
        //Free Mode
        if (params.freeMode) {
            if ( (_this.times.end - _this.times.start) < 300 && params.freeModeFluid ) {
                var newPosition = _this.positions.current + _this.touches.diff * 2 ;
                if (newPosition < maxPosition*(-1)) newPosition = -maxPosition;
                if (newPosition > 0) newPosition = 0;
                if (isHorizontal)
                    _this.setTransform( newPosition, 0, 0)
                else 
                    _this.setTransform( 0, newPosition, 0)
                    
                _this.setTransition( (_this.times.end - _this.times.start)*2 )
                _this.updateActiveSlide(newPosition)
            }
            if (!params.freeModeFluid || (_this.times.end - _this.times.start) >= 300) _this.updateActiveSlide(_this.positions.current)
            if (params.onTouchEnd) params.onTouchEnd(_this)
            _this.callPlugins('onTouchEnd');
            return
        }
        
        //Direction
        direction = diff < 0 ? "toNext" : "toPrev"
        
        //Short Touches
        if (direction=="toNext" && ( _this.times.end - _this.times.start <= 300 ) ) {
            if (diffAbs < 30 || !params.shortSwipes) _this.swipeReset()
            else _this.swipeNext(true);
        }
        
        if (direction=="toPrev" && ( _this.times.end - _this.times.start <= 300 ) ) {
        
            if (diffAbs < 30 || !params.shortSwipes) _this.swipeReset()
            else _this.swipePrev(true);
        }
        //Long Touches
        var groupSize = slideSize * params.slidesPerGroup
        if (direction=="toNext" && ( _this.times.end - _this.times.start > 300 ) ) {
            if (diffAbs >= groupSize*0.5) {
                _this.swipeNext(true)
            }
            else {
                _this.swipeReset()
            }
        }
        if (direction=="toPrev" && ( _this.times.end - _this.times.start > 300 ) ) {
            if (diffAbs >= groupSize*0.5) {
                _this.swipePrev(true);
            }
            else {
                _this.swipeReset()
            }
        }
        if (params.onTouchEnd) params.onTouchEnd(_this)
        _this.callPlugins('onTouchEnd');
    }
    
    /*=========================
      Swipe Functions
      ===========================*/
    _this.swipeNext = function(internal) {
        if (!internal && params.loop) _this.fixLoop();
        if (!internal && params.autoPlay) _this.stopAutoPlay();

        _this.callPlugins('onSwipeNext');

        var getTranslate = isHorizontal ? _this.getTranslate('x') : _this.getTranslate('y');
        var groupSize = slideSize * params.slidesPerGroup;
        var newPosition = Math.floor(Math.abs(getTranslate)/Math.floor(groupSize))*groupSize + groupSize; 
        var curPos = Math.abs(getTranslate)
        if (newPosition==wrapperSize) return;
        if (curPos >= maxPos() && !params.loop) return;
        if (newPosition > maxPos() && !params.loop) {
            newPosition = maxPos()
        };
        if (params.loop) {
            if (newPosition >= (maxPos()+containerSize)) newPosition = maxPos()+containerSize
        }
        if (isHorizontal) {
            _this.setTransform(-newPosition,0,0)
        }
        else {
            _this.setTransform(0,-newPosition,0)
        }
        
        _this.setTransition( params.speed)
        
        //Update Active Slide
        _this.updateActiveSlide(-newPosition)
        
        //Run Callbacks
        slideChangeCallbacks()
        
        return true
    }
    
    _this.swipePrev = function(internal) {
        if (!internal&&params.loop) _this.fixLoop();
        if (!internal && params.autoPlay) _this.stopAutoPlay();

        _this.callPlugins('onSwipePrev');

        var getTranslate = isHorizontal ? _this.getTranslate('x') : _this.getTranslate('y')
        
        var groupSize = slideSize * params.slidesPerGroup;
        var newPosition = (Math.ceil(-getTranslate/groupSize)-1)*groupSize;
        
        if (newPosition < 0) newPosition = 0;
        
        if (isHorizontal) {
            _this.setTransform(-newPosition,0,0)
        }
        else  {
            _this.setTransform(0,-newPosition,0)
        }       
        _this.setTransition(params.speed)
        
        //Update Active Slide
        _this.updateActiveSlide(-newPosition)
        
        //Run Callbacks
        slideChangeCallbacks()
        
        return true
    }
    
    _this.swipeReset = function(prevention) {
        _this.callPlugins('onSwipeReset');
        var getTranslate = isHorizontal ? _this.getTranslate('x') : _this.getTranslate('y');
        var groupSize = slideSize * params.slidesPerGroup
        var newPosition = getTranslate<0 ? Math.round(getTranslate/groupSize)*groupSize : 0
        var maxPosition = -maxPos()
        if (params.scrollContainer)  {
            newPosition = getTranslate<0 ? getTranslate : 0;
            maxPosition = containerSize - slideSize;
        }
        
        if (newPosition <= maxPosition) {
            newPosition = maxPosition
        }
        if (params.scrollContainer && (containerSize>slideSize)) {
            newPosition = 0;
        }
        
        if (params.mode=='horizontal') {
            _this.setTransform(newPosition,0,0)
        }
        else {
            _this.setTransform(0,newPosition,0)
        }
        
        _this.setTransition( params.speed)
        
        //Update Active Slide
        _this.updateActiveSlide(newPosition)
        
        //Reset Callback
        if (params.onSlideReset) {
            params.onSlideReset(_this)
        }
        
        return true
    }
    
    var firstTimeLoopPositioning = true;
    
    _this.swipeTo = function (index, speed, runCallbacks) { 
    
        index = parseInt(index, 10); //type cast to int
        _this.callPlugins('onSwipeTo', {index:index, speed:speed});
            
        if (index > (numOfSlides-1)) return;
        if (index<0 && !params.loop) return;
        runCallbacks = runCallbacks===false ? false : runCallbacks || true
        var speed = speed===0 ? speed : speed || params.speed;
        
        if (params.loop) index = index + params.slidesPerSlide;
        
        if (index > numOfSlides - params.slidesPerSlide) index = numOfSlides - params.slidesPerSlide;
        var newPosition =  -index*slideSize ;
        
        if(firstTimeLoopPositioning && params.loop && params.initialSlide > 0 && params.initialSlide < numOfSlides){
            newPosition = newPosition - params.initialSlide * slideSize;
            firstTimeLoopPositioning = false;
        }
        
        if (isHorizontal) {
            _this.setTransform(newPosition,0,0)
        }
        else {
            _this.setTransform(0,newPosition,0)
        }
        _this.setTransition( speed )    
        _this.updateActiveSlide(newPosition)

        //Run Callbacks
        if (runCallbacks) 
            slideChangeCallbacks()
            
        return true
    }
    
    //Prevent Multiple Callbacks 
    _this._queueStartCallbacks = false;
    _this._queueEndCallbacks = false;
    function slideChangeCallbacks() {
        //Transition Start Callback
        _this.callPlugins('onSlideChangeStart');
        if (params.onSlideChangeStart && !_this._queueStartCallbacks) {
            _this._queueStartCallbacks = true;
            params.onSlideChangeStart(_this)
            _this.transitionEnd(function(){
                _this._queueStartCallbacks = false;
            })
        }
        
        //Transition End Callback
        if (params.onSlideChangeEnd && !_this._queueEndCallbacks) {
            _this._queueEndCallbacks = true;
            _this.transitionEnd(params.onSlideChangeEnd)
        }
        
    }
    
    _this.updateActiveSlide = function(position) {
        _this.previousIndex = _this.previousSlide = _this.realIndex
        _this.realIndex = Math.round(-position/slideSize)
        if (!params.loop) _this.activeIndex = _this.realIndex;
        else {
            _this.activeIndex = _this.realIndex-params.slidesPerSlide
            if (_this.activeIndex>=numOfSlides-params.slidesPerSlide*2) {
                _this.activeIndex = numOfSlides - params.slidesPerSlide*2 - _this.activeIndex
            }
            if (_this.activeIndex<0) {
                _this.activeIndex = numOfSlides - params.slidesPerSlide*2 + _this.activeIndex   
            }
        }
        if (_this.realIndex==numOfSlides) _this.realIndex = numOfSlides-1
        if (_this.realIndex<0) _this.realIndex = 0
        //Legacy
        _this.activeSlide = _this.activeIndex;
        //Update Pagination
        if (params.pagination) {
            _this.updatePagination()
        }
        
    }
    
    
    
    /*=========================
      Loop Fixes
      ===========================*/
    _this.fixLoop = function(){     
        //Fix For Negative Oversliding
        if (_this.realIndex < params.slidesPerSlide) {
            var newIndex = numOfSlides - params.slidesPerSlide*3 + _this.realIndex;
            _this.swipeTo(newIndex,0, false)
        }
        //Fix For Positive Oversliding
        if (_this.realIndex > numOfSlides - params.slidesPerSlide*2) {
            var newIndex = -numOfSlides + _this.realIndex + params.slidesPerSlide
            _this.swipeTo(newIndex,0, false)
        }
    }
    if (params.loop) {
        _this.swipeTo(0,0, false)
    }

    

}

Swiper.prototype = {
    plugins : {},
    //Transition End
    transitionEnd : function(callback, permanent) {
        var a = this
        var el = a.wrapper
        var events = ['webkitTransitionEnd','transitionend', 'oTransitionEnd', 'MSTransitionEnd', 'msTransitionEnd'];
        if (callback) {
            function fireCallBack() {
                callback(a)
                a._queueEndCallbacks = false
                if (!permanent) {
                    for (var i=0; i<events.length; i++) {
                        el.removeEventListener(events[i], fireCallBack, false)
                    }
                }
            }
            for (var i=0; i<events.length; i++) {
                el.addEventListener(events[i], fireCallBack, false)
            }
        }
    },
    
    //Touch Support
    isSupportTouch : function() {
        return ("ontouchstart" in window) || window.DocumentTouch && document instanceof DocumentTouch;
    },
        
    // 3D Transforms Test 
    isSupport3D : function() {
        var div = document.createElement('div');
        div.id = 'test3d';
            
        var s3d=false;  
        if("webkitPerspective" in div.style) s3d=true;
        if("MozPerspective" in div.style) s3d=true;
        if("OPerspective" in div.style) s3d=true;
        if("MsPerspective" in div.style) s3d=true;
        if("perspective" in div.style) s3d=true;

        /* Test with Media query for Webkit to prevent FALSE positive*/ 
        if(s3d && ("webkitPerspective" in div.style) ) {
            var st = document.createElement('style');
            st.textContent = '@media (-webkit-transform-3d), (transform-3d), (-moz-transform-3d), (-o-transform-3d), (-ms-transform-3d) {#test3d{height:5px}}'
            document.getElementsByTagName('head')[0].appendChild(st);
            document.body.appendChild(div);
            s3d = div.offsetHeight === 5;
            st.parentNode.removeChild(st);
            div.parentNode.removeChild(div);
        }
        
        return s3d;
    },
        
    //GetTranslate
    getTranslate : function(axis){
        var el = this.wrapper
        var matrix;
        var curTransform;
        if (window.WebKitCSSMatrix) {
            var transformMatrix = new WebKitCSSMatrix(window.getComputedStyle(el, null).webkitTransform)
            matrix = transformMatrix.toString().split(',');
        }
        else {
            var transformMatrix =   window.getComputedStyle(el, null).MozTransform || window.getComputedStyle(el, null).OTransform || window.getComputedStyle(el, null).MsTransform || window.getComputedStyle(el, null).msTransform  || window.getComputedStyle(el, null).transform|| window.getComputedStyle(el, null).getPropertyValue("transform").replace("translate(", "matrix(1, 0, 0, 1,");
            matrix = transformMatrix.toString().split(',');
            
        }
        if (this.params.useCSS3Transforms) { 
            if (axis=='x') {
                //Crazy IE10 Matrix
                if (matrix.length==16) 
                    curTransform = parseFloat( matrix[12] )
                //Latest Chrome and webkits Fix
                else if (window.WebKitCSSMatrix)
                    curTransform = transformMatrix.m41
                //Normal Browsers
                else 
                    curTransform = parseFloat( matrix[4] )
            }
            if (axis=='y') {
                //Crazy IE10 Matrix
                if (matrix.length==16) 
                    curTransform = parseFloat( matrix[13] )
                //Latest Chrome and webkits Fix
                else if (window.WebKitCSSMatrix)
                    curTransform = transformMatrix.m42
                //Normal Browsers
                else 
                    curTransform = parseFloat( matrix[5] )
            }
        }
        else {
            if (axis=='x') curTransform = parseFloat(el.style.left,10) || 0
            if (axis=='y') curTransform = parseFloat(el.style.top,10) || 0
        }
        return curTransform;
    },
    
    //Set Transform
    setTransform : function(x,y,z) {
        
        var es = this.wrapper.style
        x=x||0;
        y=y||0;
        z=z||0;
        if (this.params.useCSS3Transforms) {
            if (this.support.threeD) {
                es.webkitTransform = es.MsTransform = es.msTransform = es.MozTransform = es.OTransform = es.transform = 'translate3d('+x+'px, '+y+'px, '+z+'px)'
            }
            else {
                
                es.webkitTransform = es.MsTransform = es.msTransform = es.MozTransform = es.OTransform = es.transform = 'translate('+x+'px, '+y+'px)'
                if (this.ie8) {
                    es.left = x+'px'
                    es.top = y+'px'
                }
            }
        }
        else {
            es.left = x+'px';
            es.top = y+'px';
        }
        this.callPlugins('onSetTransform', {x:x, y:y, z:z})
    },
    
    //Set Transition
    setTransition : function(duration) {
        var es = this.wrapper.style
        es.webkitTransitionDuration = es.MsTransitionDuration = es.msTransitionDuration = es.MozTransitionDuration = es.OTransitionDuration = es.transitionDuration = duration/1000+'s';
        this.callPlugins('onSetTransition', {duration: duration})
    },
    
    //Check for IE8
    ie8: (function(){
        var rv = -1; // Return value assumes failure.
        if (navigator.appName == 'Microsoft Internet Explorer') {
            var ua = navigator.userAgent;
            var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat(RegExp.$1);
        }
        return rv != -1 && rv < 9;
    })(),
    
    ie10 : window.navigator.msPointerEnabled
}

/*=========================
  jQuery & Zepto Plugins
  ===========================*/   
if (window.jQuery||window.Zepto) {
    (function($){
        $.fn.swiper = function(params) {
            var s = new Swiper($(this)[0], params)
            $(this).data('swiper',s);
            return s
        }
    })(window.jQuery||window.Zepto)
}
/*
* FitVids 1.1
*
* Copyright 2013, Chris Coyier - http://css-tricks.com + Dave Rupert - http://daverupert.com
* Credit to Thierry Koblentz - http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
* Released under the WTFPL license - http://sam.zoy.org/wtfpl/
*
*/
;(function(a){a.fn.fitVids=function(b){var e={customSelector:null,ignore:null};if(!document.getElementById("fit-vids-style")){var d=document.head||document.getElementsByTagName("head")[0];var c=".fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}";var f=document.createElement("div");f.innerHTML='<p>x</p><style id="fit-vids-style">'+c+"</style>";d.appendChild(f.childNodes[1])}if(b){a.extend(e,b)}return this.each(function(){var g=['iframe[src*="player.vimeo.com"]','iframe[src*="youtube.com"]','iframe[src*="youtube-nocookie.com"]','iframe[src*="kickstarter.com"][src*="video.html"]',"object","embed"];if(e.customSelector){g.push(e.customSelector)}var h=".fitvidsignore";if(e.ignore){h=h+", "+e.ignore}var i=a(this).find(g.join(","));i=i.not("object object");i=i.not(h);i.each(function(){var n=a(this);if(n.parents(h).length>0){return}if(this.tagName.toLowerCase()==="embed"&&n.parent("object").length||n.parent(".fluid-width-video-wrapper").length){return}if((!n.css("height")&&!n.css("width"))&&(isNaN(n.attr("height"))||isNaN(n.attr("width")))){n.attr("height",9);n.attr("width",16)}var j=(this.tagName.toLowerCase()==="object"||(n.attr("height")&&!isNaN(parseInt(n.attr("height"),10))))?parseInt(n.attr("height"),10):n.height(),k=!isNaN(parseInt(n.attr("width"),10))?parseInt(n.attr("width"),10):n.width(),l=j/k;if(!n.attr("id")){var m="fitvid"+Math.floor(Math.random()*999999);n.attr("id",m)}n.wrap('<div class="fluid-width-video-wrapper"></div>').parent(".fluid-width-video-wrapper").css("padding-top",(l*100)+"%");n.removeAttr("height").removeAttr("width")})})}})(window.jQuery||window.Zepto);
/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));
/*!
 * jQuery resize event - v1.1 - 3/14/2010
 * http://benalman.com/projects/jquery-resize-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

// Script: jQuery resize event
//
// *Version: 1.1, Last updated: 3/14/2010*
// 
// Project Home - http://benalman.com/projects/jquery-resize-plugin/
// GitHub       - http://github.com/cowboy/jquery-resize/
// Source       - http://github.com/cowboy/jquery-resize/raw/master/jquery.ba-resize.js
// (Minified)   - http://github.com/cowboy/jquery-resize/raw/master/jquery.ba-resize.min.js (1.0kb)
// 
// About: License
// 
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
// 
// About: Examples
// 
// This working example, complete with fully commented code, illustrates a few
// ways in which this plugin can be used.
// 
// resize event - http://benalman.com/code/projects/jquery-resize/examples/resize/
// 
// About: Support and Testing
// 
// Information about what version or versions of jQuery this plugin has been
// tested with, what browsers it has been tested in, and where the unit tests
// reside (so you can test it yourself).
// 
// jQuery Versions - 1.3.2, 1.4.1, 1.4.2
// Browsers Tested - Internet Explorer 6-8, Firefox 2-3.6, Safari 3-4, Chrome, Opera 9.6-10.1.
// Unit Tests      - http://benalman.com/code/projects/jquery-resize/unit/
// 
// About: Release History
// 
// 1.1 - (3/14/2010) Fixed a minor bug that was causing the event to trigger
//       immediately after bind in some circumstances. Also changed $.fn.data
//       to $.data to improve performance.
// 1.0 - (2/10/2010) Initial release

(function($,window,undefined){
  '$:nomunge'; // Used by YUI compressor.
  
  // A jQuery object containing all non-window elements to which the resize
  // event is bound.
  var elems = $([]),
    
    // Extend $.resize if it already exists, otherwise create it.
    jq_resize = $.resize = $.extend( $.resize, {} ),
    
    timeout_id,
    
    // Reused strings.
    str_setTimeout = 'setTimeout',
    str_resize = 'resize',
    str_data = str_resize + '-special-event',
    str_delay = 'delay',
    str_throttle = 'throttleWindow';
  
  // Property: jQuery.resize.delay
  // 
  // The numeric interval (in milliseconds) at which the resize event polling
  // loop executes. Defaults to 250.
  
  jq_resize[ str_delay ] = 250;
  
  // Property: jQuery.resize.throttleWindow
  // 
  // Throttle the native window object resize event to fire no more than once
  // every <jQuery.resize.delay> milliseconds. Defaults to true.
  // 
  // Because the window object has its own resize event, it doesn't need to be
  // provided by this plugin, and its execution can be left entirely up to the
  // browser. However, since certain browsers fire the resize event continuously
  // while others do not, enabling this will throttle the window resize event,
  // making event behavior consistent across all elements in all browsers.
  // 
  // While setting this property to false will disable window object resize
  // event throttling, please note that this property must be changed before any
  // window object resize event callbacks are bound.
  
  jq_resize[ str_throttle ] = true;
  
  // Event: resize event
  // 
  // Fired when an element's width or height changes. Because browsers only
  // provide this event for the window element, for other elements a polling
  // loop is initialized, running every <jQuery.resize.delay> milliseconds
  // to see if elements' dimensions have changed. You may bind with either
  // .resize( fn ) or .bind( "resize", fn ), and unbind with .unbind( "resize" ).
  // 
  // Usage:
  // 
  // > jQuery('selector').bind( 'resize', function(e) {
  // >   // element's width or height has changed!
  // >   ...
  // > });
  // 
  // Additional Notes:
  // 
  // * The polling loop is not created until at least one callback is actually
  //   bound to the 'resize' event, and this single polling loop is shared
  //   across all elements.
  // 
  // Double firing issue in jQuery 1.3.2:
  // 
  // While this plugin works in jQuery 1.3.2, if an element's event callbacks
  // are manually triggered via .trigger( 'resize' ) or .resize() those
  // callbacks may double-fire, due to limitations in the jQuery 1.3.2 special
  // events system. This is not an issue when using jQuery 1.4+.
  // 
  // > // While this works in jQuery 1.4+
  // > $(elem).css({ width: new_w, height: new_h }).resize();
  // > 
  // > // In jQuery 1.3.2, you need to do this:
  // > var elem = $(elem);
  // > elem.css({ width: new_w, height: new_h });
  // > elem.data( 'resize-special-event', { width: elem.width(), height: elem.height() } );
  // > elem.resize();
      
  $.event.special[ str_resize ] = {
    
    // Called only when the first 'resize' event callback is bound per element.
    setup: function() {
      // Since window has its own native 'resize' event, return false so that
      // jQuery will bind the event using DOM methods. Since only 'window'
      // objects have a .setTimeout method, this should be a sufficient test.
      // Unless, of course, we're throttling the 'resize' event for window.
      if ( !jq_resize[ str_throttle ] && this[ str_setTimeout ] ) { return false; }
      
      var elem = $(this);
      
      // Add this element to the list of internal elements to monitor.
      elems = elems.add( elem );
      
      // Initialize data store on the element.
      $.data( this, str_data, { w: elem.width(), h: elem.height() } );
      
      // If this is the first element added, start the polling loop.
      if ( elems.length === 1 ) {
        loopy();
      }
    },
    
    // Called only when the last 'resize' event callback is unbound per element.
    teardown: function() {
      // Since window has its own native 'resize' event, return false so that
      // jQuery will unbind the event using DOM methods. Since only 'window'
      // objects have a .setTimeout method, this should be a sufficient test.
      // Unless, of course, we're throttling the 'resize' event for window.
      if ( !jq_resize[ str_throttle ] && this[ str_setTimeout ] ) { return false; }
      
      var elem = $(this);
      
      // Remove this element from the list of internal elements to monitor.
      elems = elems.not( elem );
      
      // Remove any data stored on the element.
      elem.removeData( str_data );
      
      // If this is the last element removed, stop the polling loop.
      if ( !elems.length ) {
        clearTimeout( timeout_id );
      }
    },
    
    // Called every time a 'resize' event callback is bound per element (new in
    // jQuery 1.4).
    add: function( handleObj ) {
      // Since window has its own native 'resize' event, return false so that
      // jQuery doesn't modify the event object. Unless, of course, we're
      // throttling the 'resize' event for window.
      if ( !jq_resize[ str_throttle ] && this[ str_setTimeout ] ) { return false; }
      
      var old_handler;
      
      // The new_handler function is executed every time the event is triggered.
      // This is used to update the internal element data store with the width
      // and height when the event is triggered manually, to avoid double-firing
      // of the event callback. See the "Double firing issue in jQuery 1.3.2"
      // comments above for more information.
      
      function new_handler( e, w, h ) {
        var elem = $(this),
            data = $.data( this, str_data );    
               
            // fix related to https://github.com/cowboy/jquery-resize/issues/1
            if (data == null) {
            data = { w: null, h: null };
            }
        
        // If called from the polling loop, w and h will be passed in as
        // arguments. If called manually, via .trigger( 'resize' ) or .resize(),
        // those values will need to be computed.
        data.w = w !== undefined ? w : elem.width();
        data.h = h !== undefined ? h : elem.height();
        
        old_handler.apply( this, arguments );
      };
      
      // This may seem a little complicated, but it normalizes the special event
      // .add method between jQuery 1.4/1.4.1 and 1.4.2+
      if ( $.isFunction( handleObj ) ) {
        // 1.4, 1.4.1
        old_handler = handleObj;
        return new_handler;
      } else {
        // 1.4.2+
        old_handler = handleObj.handler;
        handleObj.handler = new_handler;
      }
    }
    
  };
  
  function loopy() {
    
    // Start the polling loop, asynchronously.
    timeout_id = window[ str_setTimeout ](function(){
      
      // Iterate over all elements to which the 'resize' event is bound.
      elems.each(function(){
        var elem = $(this),
          width = elem.width(),
          height = elem.height(),
          data = $.data( this, str_data );
        
        // If element size has changed since the last time, update the element
        // data store and trigger the 'resize' event.
        if ( width !== data.w || height !== data.h ) {
          elem.trigger( str_resize, [ data.w = width, data.h = height ] );
        }
        
      });
      
      // Loop.
      loopy();
      
    }, jq_resize[ str_delay ] );
    
  };
  
})(jQuery,this);
/*
* touchSwipe - jQuery Plugin
* https://github.com/mattbryson/TouchSwipe-Jquery-Plugin
* http://labs.skinkers.com/touchSwipe/
* http://plugins.jquery.com/project/touchSwipe
*
* Copyright (c) 2010 Matt Bryson (www.skinkers.com)
* Dual licensed under the MIT or GPL Version 2 licenses.
*
* $version: 1.3.3
*/

(function(g){function P(c){if(c&&void 0===c.allowPageScroll&&(void 0!==c.swipe||void 0!==c.swipeStatus))c.allowPageScroll=G;c||(c={});c=g.extend({},g.fn.swipe.defaults,c);return this.each(function(){var b=g(this),f=b.data(w);f||(f=new W(this,c),b.data(w,f))})}function W(c,b){var f,p,r,s;function H(a){var a=a.originalEvent,c,Q=n?a.touches[0]:a;d=R;n?h=a.touches.length:a.preventDefault();i=0;j=null;k=0;!n||h===b.fingers||b.fingers===x?(r=f=Q.pageX,s=p=Q.pageY,y=(new Date).getTime(),b.swipeStatus&&(c= l(a,d))):t(a);if(!1===c)return d=m,l(a,d),c;e.bind(I,J);e.bind(K,L)}function J(a){a=a.originalEvent;if(!(d===q||d===m)){var c,e=n?a.touches[0]:a;f=e.pageX;p=e.pageY;u=(new Date).getTime();j=S();n&&(h=a.touches.length);d=z;var e=a,g=j;if(b.allowPageScroll===G)e.preventDefault();else{var o=b.allowPageScroll===T;switch(g){case v:(b.swipeLeft&&o||!o&&b.allowPageScroll!=M)&&e.preventDefault();break;case A:(b.swipeRight&&o||!o&&b.allowPageScroll!=M)&&e.preventDefault();break;case B:(b.swipeUp&&o||!o&&b.allowPageScroll!= N)&&e.preventDefault();break;case C:(b.swipeDown&&o||!o&&b.allowPageScroll!=N)&&e.preventDefault()}}h===b.fingers||b.fingers===x||!n?(i=U(),k=u-y,b.swipeStatus&&(c=l(a,d,j,i,k)),b.triggerOnTouchEnd||(e=!(b.maxTimeThreshold?!(k>=b.maxTimeThreshold):1),!0===D()?(d=q,c=l(a,d)):e&&(d=m,l(a,d)))):(d=m,l(a,d));!1===c&&(d=m,l(a,d))}}function L(a){a=a.originalEvent;a.preventDefault();u=(new Date).getTime();i=U();j=S();k=u-y;if(b.triggerOnTouchEnd||!1===b.triggerOnTouchEnd&&d===z)if(d=q,(h===b.fingers||b.fingers=== x||!n)&&0!==f){var c=!(b.maxTimeThreshold?!(k>=b.maxTimeThreshold):1);if((!0===D()||null===D())&&!c)l(a,d);else if(c||!1===D())d=m,l(a,d)}else d=m,l(a,d);else d===z&&(d=m,l(a,d));e.unbind(I,J,!1);e.unbind(K,L,!1)}function t(){y=u=p=f=s=r=h=0}function l(a,c){var d=void 0;b.swipeStatus&&(d=b.swipeStatus.call(e,a,c,j||null,i||0,k||0,h));if(c===m&&b.click&&(1===h||!n)&&(isNaN(i)||0===i))d=b.click.call(e,a,a.target);if(c==q)switch(b.swipe&&(d=b.swipe.call(e,a,j,i,k,h)),j){case v:b.swipeLeft&&(d=b.swipeLeft.call(e, a,j,i,k,h));break;case A:b.swipeRight&&(d=b.swipeRight.call(e,a,j,i,k,h));break;case B:b.swipeUp&&(d=b.swipeUp.call(e,a,j,i,k,h));break;case C:b.swipeDown&&(d=b.swipeDown.call(e,a,j,i,k,h))}(c===m||c===q)&&t(a);return d}function D(){return null!==b.threshold?i>=b.threshold:null}function U(){return Math.round(Math.sqrt(Math.pow(f-r,2)+Math.pow(p-s,2)))}function S(){var a;a=Math.atan2(p-s,r-f);a=Math.round(180*a/Math.PI);0>a&&(a=360-Math.abs(a));return 45>=a&&0<=a?v:360>=a&&315<=a?v:135<=a&&225>=a? A:45<a&&135>a?C:B}function V(){e.unbind(E,H);e.unbind(F,t);e.unbind(I,J);e.unbind(K,L)}var O=n||!b.fallbackToMouseEvents,E=O?"touchstart":"mousedown",I=O?"touchmove":"mousemove",K=O?"touchend":"mouseup",F="touchcancel",i=0,j=null,k=0,e=g(c),d="start",h=0,y=p=f=s=r=0,u=0;try{e.bind(E,H),e.bind(F,t)}catch(P){g.error("events not supported "+E+","+F+" on jQuery.swipe")}this.enable=function(){e.bind(E,H);e.bind(F,t);return e};this.disable=function(){V();return e};this.destroy=function(){V();e.data(w,null); return e}}var v="left",A="right",B="up",C="down",G="none",T="auto",M="horizontal",N="vertical",x="all",R="start",z="move",q="end",m="cancel",n="ontouchstart"in window,w="TouchSwipe";g.fn.swipe=function(c){var b=g(this),f=b.data(w);if(f&&"string"===typeof c){if(f[c])return f[c].apply(this,Array.prototype.slice.call(arguments,1));g.error("Method "+c+" does not exist on jQuery.swipe")}else if(!f&&("object"===typeof c||!c))return P.apply(this,arguments);return b};g.fn.swipe.defaults={fingers:1,threshold:75, maxTimeThreshold:null,swipe:null,swipeLeft:null,swipeRight:null,swipeUp:null,swipeDown:null,swipeStatus:null,click:null,triggerOnTouchEnd:!0,allowPageScroll:"auto",fallbackToMouseEvents:!0};g.fn.swipe.phases={PHASE_START:R,PHASE_MOVE:z,PHASE_END:q,PHASE_CANCEL:m};g.fn.swipe.directions={LEFT:v,RIGHT:A,UP:B,DOWN:C};g.fn.swipe.pageScroll={NONE:G,HORIZONTAL:M,VERTICAL:N,AUTO:T};g.fn.swipe.fingers={ONE:1,TWO:2,THREE:3,ALL:x}})(jQuery);
/*
 *	jQuery carouFredSel 6.2.1
 *	Demo's and documentation:
 *	caroufredsel.dev7studios.com
 *
 *	Copyright (c) 2013 Fred Heusschen
 *	www.frebsite.nl
 *
 *	Dual licensed under the MIT and GPL licenses.
 *	http://en.wikipedia.org/wiki/MIT_License
 *	http://en.wikipedia.org/wiki/GNU_General_Public_License
 */


(function($){function sc_setScroll(a,b,c){return"transition"==c.transition&&"swing"==b&&(b="ease"),{anims:[],duration:a,orgDuration:a,easing:b,startTime:getTime()}}function sc_startScroll(a,b){for(var c=0,d=a.anims.length;d>c;c++){var e=a.anims[c];e&&e[0][b.transition](e[1],a.duration,a.easing,e[2])}}function sc_stopScroll(a,b){is_boolean(b)||(b=!0),is_object(a.pre)&&sc_stopScroll(a.pre,b);for(var c=0,d=a.anims.length;d>c;c++){var e=a.anims[c];e[0].stop(!0),b&&(e[0].css(e[1]),is_function(e[2])&&e[2]())}is_object(a.post)&&sc_stopScroll(a.post,b)}function sc_afterScroll(a,b,c){switch(b&&b.remove(),c.fx){case"fade":case"crossfade":case"cover-fade":case"uncover-fade":a.css("opacity",1),a.css("filter","")}}function sc_fireCallbacks(a,b,c,d,e){if(b[c]&&b[c].call(a,d),e[c].length)for(var f=0,g=e[c].length;g>f;f++)e[c][f].call(a,d);return[]}function sc_fireQueue(a,b,c){return b.length&&(a.trigger(cf_e(b[0][0],c),b[0][1]),b.shift()),b}function sc_hideHiddenItems(a){a.each(function(){var a=$(this);a.data("_cfs_isHidden",a.is(":hidden")).hide()})}function sc_showHiddenItems(a){a&&a.each(function(){var a=$(this);a.data("_cfs_isHidden")||a.show()})}function sc_clearTimers(a){return a.auto&&clearTimeout(a.auto),a.progress&&clearInterval(a.progress),a}function sc_mapCallbackArguments(a,b,c,d,e,f,g){return{width:g.width,height:g.height,items:{old:a,skipped:b,visible:c},scroll:{items:d,direction:e,duration:f}}}function sc_getDuration(a,b,c,d){var e=a.duration;return"none"==a.fx?0:("auto"==e?e=b.scroll.duration/b.scroll.items*c:10>e&&(e=d/e),1>e?0:("fade"==a.fx&&(e/=2),Math.round(e)))}function nv_showNavi(a,b,c){var d=is_number(a.items.minimum)?a.items.minimum:a.items.visible+1;if("show"==b||"hide"==b)var e=b;else if(d>b){debug(c,"Not enough items ("+b+" total, "+d+" needed): Hiding navigation.");var e="hide"}else var e="show";var f="show"==e?"removeClass":"addClass",g=cf_c("hidden",c);a.auto.button&&a.auto.button[e]()[f](g),a.prev.button&&a.prev.button[e]()[f](g),a.next.button&&a.next.button[e]()[f](g),a.pagination.container&&a.pagination.container[e]()[f](g)}function nv_enableNavi(a,b,c){if(!a.circular&&!a.infinite){var d="removeClass"==b||"addClass"==b?b:!1,e=cf_c("disabled",c);if(a.auto.button&&d&&a.auto.button[d](e),a.prev.button){var f=d||0==b?"addClass":"removeClass";a.prev.button[f](e)}if(a.next.button){var f=d||b==a.items.visible?"addClass":"removeClass";a.next.button[f](e)}}}function go_getObject(a,b){return is_function(b)?b=b.call(a):is_undefined(b)&&(b={}),b}function go_getItemsObject(a,b){return b=go_getObject(a,b),is_number(b)?b={visible:b}:"variable"==b?b={visible:b,width:b,height:b}:is_object(b)||(b={}),b}function go_getScrollObject(a,b){return b=go_getObject(a,b),is_number(b)?b=50>=b?{items:b}:{duration:b}:is_string(b)?b={easing:b}:is_object(b)||(b={}),b}function go_getNaviObject(a,b){if(b=go_getObject(a,b),is_string(b)){var c=cf_getKeyCode(b);b=-1==c?$(b):c}return b}function go_getAutoObject(a,b){return b=go_getNaviObject(a,b),is_jquery(b)?b={button:b}:is_boolean(b)?b={play:b}:is_number(b)&&(b={timeoutDuration:b}),b.progress&&(is_string(b.progress)||is_jquery(b.progress))&&(b.progress={bar:b.progress}),b}function go_complementAutoObject(a,b){return is_function(b.button)&&(b.button=b.button.call(a)),is_string(b.button)&&(b.button=$(b.button)),is_boolean(b.play)||(b.play=!0),is_number(b.delay)||(b.delay=0),is_undefined(b.pauseOnEvent)&&(b.pauseOnEvent=!0),is_boolean(b.pauseOnResize)||(b.pauseOnResize=!0),is_number(b.timeoutDuration)||(b.timeoutDuration=10>b.duration?2500:5*b.duration),b.progress&&(is_function(b.progress.bar)&&(b.progress.bar=b.progress.bar.call(a)),is_string(b.progress.bar)&&(b.progress.bar=$(b.progress.bar)),b.progress.bar?(is_function(b.progress.updater)||(b.progress.updater=$.fn.carouFredSel.progressbarUpdater),is_number(b.progress.interval)||(b.progress.interval=50)):b.progress=!1),b}function go_getPrevNextObject(a,b){return b=go_getNaviObject(a,b),is_jquery(b)?b={button:b}:is_number(b)&&(b={key:b}),b}function go_complementPrevNextObject(a,b){return is_function(b.button)&&(b.button=b.button.call(a)),is_string(b.button)&&(b.button=$(b.button)),is_string(b.key)&&(b.key=cf_getKeyCode(b.key)),b}function go_getPaginationObject(a,b){return b=go_getNaviObject(a,b),is_jquery(b)?b={container:b}:is_boolean(b)&&(b={keys:b}),b}function go_complementPaginationObject(a,b){return is_function(b.container)&&(b.container=b.container.call(a)),is_string(b.container)&&(b.container=$(b.container)),is_number(b.items)||(b.items=!1),is_boolean(b.keys)||(b.keys=!1),is_function(b.anchorBuilder)||is_false(b.anchorBuilder)||(b.anchorBuilder=$.fn.carouFredSel.pageAnchorBuilder),is_number(b.deviation)||(b.deviation=0),b}function go_getSwipeObject(a,b){return is_function(b)&&(b=b.call(a)),is_undefined(b)&&(b={onTouch:!1}),is_true(b)?b={onTouch:b}:is_number(b)&&(b={items:b}),b}function go_complementSwipeObject(a,b){return is_boolean(b.onTouch)||(b.onTouch=!0),is_boolean(b.onMouse)||(b.onMouse=!1),is_object(b.options)||(b.options={}),is_boolean(b.options.triggerOnTouchEnd)||(b.options.triggerOnTouchEnd=!1),b}function go_getMousewheelObject(a,b){return is_function(b)&&(b=b.call(a)),is_true(b)?b={}:is_number(b)?b={items:b}:is_undefined(b)&&(b=!1),b}function go_complementMousewheelObject(a,b){return b}function gn_getItemIndex(a,b,c,d,e){if(is_string(a)&&(a=$(a,e)),is_object(a)&&(a=$(a,e)),is_jquery(a)?(a=e.children().index(a),is_boolean(c)||(c=!1)):is_boolean(c)||(c=!0),is_number(a)||(a=0),is_number(b)||(b=0),c&&(a+=d.first),a+=b,d.total>0){for(;a>=d.total;)a-=d.total;for(;0>a;)a+=d.total}return a}function gn_getVisibleItemsPrev(a,b,c){for(var d=0,e=0,f=c;f>=0;f--){var g=a.eq(f);if(d+=g.is(":visible")?g[b.d.outerWidth](!0):0,d>b.maxDimension)return e;0==f&&(f=a.length),e++}}function gn_getVisibleItemsPrevFilter(a,b,c){return gn_getItemsPrevFilter(a,b.items.filter,b.items.visibleConf.org,c)}function gn_getScrollItemsPrevFilter(a,b,c,d){return gn_getItemsPrevFilter(a,b.items.filter,d,c)}function gn_getItemsPrevFilter(a,b,c,d){for(var e=0,f=0,g=d,h=a.length;g>=0;g--){if(f++,f==h)return f;var i=a.eq(g);if(i.is(b)&&(e++,e==c))return f;0==g&&(g=h)}}function gn_getVisibleOrg(a,b){return b.items.visibleConf.org||a.children().slice(0,b.items.visible).filter(b.items.filter).length}function gn_getVisibleItemsNext(a,b,c){for(var d=0,e=0,f=c,g=a.length-1;g>=f;f++){var h=a.eq(f);if(d+=h.is(":visible")?h[b.d.outerWidth](!0):0,d>b.maxDimension)return e;if(e++,e==g+1)return e;f==g&&(f=-1)}}function gn_getVisibleItemsNextTestCircular(a,b,c,d){var e=gn_getVisibleItemsNext(a,b,c);return b.circular||c+e>d&&(e=d-c),e}function gn_getVisibleItemsNextFilter(a,b,c){return gn_getItemsNextFilter(a,b.items.filter,b.items.visibleConf.org,c,b.circular)}function gn_getScrollItemsNextFilter(a,b,c,d){return gn_getItemsNextFilter(a,b.items.filter,d+1,c,b.circular)-1}function gn_getItemsNextFilter(a,b,c,d){for(var f=0,g=0,h=d,i=a.length-1;i>=h;h++){if(g++,g>=i)return g;var j=a.eq(h);if(j.is(b)&&(f++,f==c))return g;h==i&&(h=-1)}}function gi_getCurrentItems(a,b){return a.slice(0,b.items.visible)}function gi_getOldItemsPrev(a,b,c){return a.slice(c,b.items.visibleConf.old+c)}function gi_getNewItemsPrev(a,b){return a.slice(0,b.items.visible)}function gi_getOldItemsNext(a,b){return a.slice(0,b.items.visibleConf.old)}function gi_getNewItemsNext(a,b,c){return a.slice(c,b.items.visible+c)}function sz_storeMargin(a,b,c){b.usePadding&&(is_string(c)||(c="_cfs_origCssMargin"),a.each(function(){var a=$(this),d=parseInt(a.css(b.d.marginRight),10);is_number(d)||(d=0),a.data(c,d)}))}function sz_resetMargin(a,b,c){if(b.usePadding){var d=is_boolean(c)?c:!1;is_number(c)||(c=0),sz_storeMargin(a,b,"_cfs_tempCssMargin"),a.each(function(){var a=$(this);a.css(b.d.marginRight,d?a.data("_cfs_tempCssMargin"):c+a.data("_cfs_origCssMargin"))})}}function sz_storeOrigCss(a){a.each(function(){var a=$(this);a.data("_cfs_origCss",a.attr("style")||"")})}function sz_restoreOrigCss(a){a.each(function(){var a=$(this);a.attr("style",a.data("_cfs_origCss")||"")})}function sz_setResponsiveSizes(a,b){var d=(a.items.visible,a.items[a.d.width]),e=a[a.d.height],f=is_percentage(e);b.each(function(){var b=$(this),c=d-ms_getPaddingBorderMargin(b,a,"Width");b[a.d.width](c),f&&b[a.d.height](ms_getPercentage(c,e))})}function sz_setSizes(a,b){var c=a.parent(),d=a.children(),e=gi_getCurrentItems(d,b),f=cf_mapWrapperSizes(ms_getSizes(e,b,!0),b,!1);if(c.css(f),b.usePadding){var g=b.padding,h=g[b.d[1]];b.align&&0>h&&(h=0);var i=e.last();i.css(b.d.marginRight,i.data("_cfs_origCssMargin")+h),a.css(b.d.top,g[b.d[0]]),a.css(b.d.left,g[b.d[3]])}return a.css(b.d.width,f[b.d.width]+2*ms_getTotalSize(d,b,"width")),a.css(b.d.height,ms_getLargestSize(d,b,"height")),f}function ms_getSizes(a,b,c){return[ms_getTotalSize(a,b,"width",c),ms_getLargestSize(a,b,"height",c)]}function ms_getLargestSize(a,b,c,d){return is_boolean(d)||(d=!1),is_number(b[b.d[c]])&&d?b[b.d[c]]:is_number(b.items[b.d[c]])?b.items[b.d[c]]:(c=c.toLowerCase().indexOf("width")>-1?"outerWidth":"outerHeight",ms_getTrueLargestSize(a,b,c))}function ms_getTrueLargestSize(a,b,c){for(var d=0,e=0,f=a.length;f>e;e++){var g=a.eq(e),h=g.is(":visible")?g[b.d[c]](!0):0;h>d&&(d=h)}return d}function ms_getTotalSize(a,b,c,d){if(is_boolean(d)||(d=!1),is_number(b[b.d[c]])&&d)return b[b.d[c]];if(is_number(b.items[b.d[c]]))return b.items[b.d[c]]*a.length;for(var e=c.toLowerCase().indexOf("width")>-1?"outerWidth":"outerHeight",f=0,g=0,h=a.length;h>g;g++){var i=a.eq(g);f+=i.is(":visible")?i[b.d[e]](!0):0}return f}function ms_getParentSize(a,b,c){var d=a.is(":visible");d&&a.hide();var e=a.parent()[b.d[c]]();return d&&a.show(),e}function ms_getMaxDimension(a,b){return is_number(a[a.d.width])?a[a.d.width]:b}function ms_hasVariableSizes(a,b,c){for(var d=!1,e=!1,f=0,g=a.length;g>f;f++){var h=a.eq(f),i=h.is(":visible")?h[b.d[c]](!0):0;d===!1?d=i:d!=i&&(e=!0),0==d&&(e=!0)}return e}function ms_getPaddingBorderMargin(a,b,c){return a[b.d["outer"+c]](!0)-a[b.d[c.toLowerCase()]]()}function ms_getPercentage(a,b){if(is_percentage(b)){if(b=parseInt(b.slice(0,-1),10),!is_number(b))return a;a*=b/100}return a}function cf_e(a,b,c,d,e){return is_boolean(c)||(c=!0),is_boolean(d)||(d=!0),is_boolean(e)||(e=!1),c&&(a=b.events.prefix+a),d&&(a=a+"."+b.events.namespace),d&&e&&(a+=b.serialNumber),a}function cf_c(a,b){return is_string(b.classnames[a])?b.classnames[a]:a}function cf_mapWrapperSizes(a,b,c){is_boolean(c)||(c=!0);var d=b.usePadding&&c?b.padding:[0,0,0,0],e={};return e[b.d.width]=a[0]+d[1]+d[3],e[b.d.height]=a[1]+d[0]+d[2],e}function cf_sortParams(a,b){for(var c=[],d=0,e=a.length;e>d;d++)for(var f=0,g=b.length;g>f;f++)if(b[f].indexOf(typeof a[d])>-1&&is_undefined(c[f])){c[f]=a[d];break}return c}function cf_getPadding(a){if(is_undefined(a))return[0,0,0,0];if(is_number(a))return[a,a,a,a];if(is_string(a)&&(a=a.split("px").join("").split("em").join("").split(" ")),!is_array(a))return[0,0,0,0];for(var b=0;4>b;b++)a[b]=parseInt(a[b],10);switch(a.length){case 0:return[0,0,0,0];case 1:return[a[0],a[0],a[0],a[0]];case 2:return[a[0],a[1],a[0],a[1]];case 3:return[a[0],a[1],a[2],a[1]];default:return[a[0],a[1],a[2],a[3]]}}function cf_getAlignPadding(a,b){var c=is_number(b[b.d.width])?Math.ceil(b[b.d.width]-ms_getTotalSize(a,b,"width")):0;switch(b.align){case"left":return[0,c];case"right":return[c,0];case"center":default:return[Math.ceil(c/2),Math.floor(c/2)]}}function cf_getDimensions(a){for(var b=[["width","innerWidth","outerWidth","height","innerHeight","outerHeight","left","top","marginRight",0,1,2,3],["height","innerHeight","outerHeight","width","innerWidth","outerWidth","top","left","marginBottom",3,2,1,0]],c=b[0].length,d="right"==a.direction||"left"==a.direction?0:1,e={},f=0;c>f;f++)e[b[0][f]]=b[d][f];return e}function cf_getAdjust(a,b,c,d){var e=a;if(is_function(c))e=c.call(d,e);else if(is_string(c)){var f=c.split("+"),g=c.split("-");if(g.length>f.length)var h=!0,i=g[0],j=g[1];else var h=!1,i=f[0],j=f[1];switch(i){case"even":e=1==a%2?a-1:a;break;case"odd":e=0==a%2?a-1:a;break;default:e=a}j=parseInt(j,10),is_number(j)&&(h&&(j=-j),e+=j)}return(!is_number(e)||1>e)&&(e=1),e}function cf_getItemsAdjust(a,b,c,d){return cf_getItemAdjustMinMax(cf_getAdjust(a,b,c,d),b.items.visibleConf)}function cf_getItemAdjustMinMax(a,b){return is_number(b.min)&&b.min>a&&(a=b.min),is_number(b.max)&&a>b.max&&(a=b.max),1>a&&(a=1),a}function cf_getSynchArr(a){is_array(a)||(a=[[a]]),is_array(a[0])||(a=[a]);for(var b=0,c=a.length;c>b;b++)is_string(a[b][0])&&(a[b][0]=$(a[b][0])),is_boolean(a[b][1])||(a[b][1]=!0),is_boolean(a[b][2])||(a[b][2]=!0),is_number(a[b][3])||(a[b][3]=0);return a}function cf_getKeyCode(a){return"right"==a?39:"left"==a?37:"up"==a?38:"down"==a?40:-1}function cf_setCookie(a,b,c){if(a){var d=b.triggerHandler(cf_e("currentPosition",c));$.fn.carouFredSel.cookie.set(a,d)}}function cf_getCookie(a){var b=$.fn.carouFredSel.cookie.get(a);return""==b?0:b}function in_mapCss(a,b){for(var c={},d=0,e=b.length;e>d;d++)c[b[d]]=a.css(b[d]);return c}function in_complementItems(a,b,c,d){return is_object(a.visibleConf)||(a.visibleConf={}),is_object(a.sizesConf)||(a.sizesConf={}),0==a.start&&is_number(d)&&(a.start=d),is_object(a.visible)?(a.visibleConf.min=a.visible.min,a.visibleConf.max=a.visible.max,a.visible=!1):is_string(a.visible)?("variable"==a.visible?a.visibleConf.variable=!0:a.visibleConf.adjust=a.visible,a.visible=!1):is_function(a.visible)&&(a.visibleConf.adjust=a.visible,a.visible=!1),is_string(a.filter)||(a.filter=c.filter(":hidden").length>0?":visible":"*"),a[b.d.width]||(b.responsive?(debug(!0,"Set a "+b.d.width+" for the items!"),a[b.d.width]=ms_getTrueLargestSize(c,b,"outerWidth")):a[b.d.width]=ms_hasVariableSizes(c,b,"outerWidth")?"variable":c[b.d.outerWidth](!0)),a[b.d.height]||(a[b.d.height]=ms_hasVariableSizes(c,b,"outerHeight")?"variable":c[b.d.outerHeight](!0)),a.sizesConf.width=a.width,a.sizesConf.height=a.height,a}function in_complementVisibleItems(a,b){return"variable"==a.items[a.d.width]&&(a.items.visibleConf.variable=!0),a.items.visibleConf.variable||(is_number(a[a.d.width])?a.items.visible=Math.floor(a[a.d.width]/a.items[a.d.width]):(a.items.visible=Math.floor(b/a.items[a.d.width]),a[a.d.width]=a.items.visible*a.items[a.d.width],a.items.visibleConf.adjust||(a.align=!1)),("Infinity"==a.items.visible||1>a.items.visible)&&(debug(!0,'Not a valid number of visible items: Set to "variable".'),a.items.visibleConf.variable=!0)),a}function in_complementPrimarySize(a,b,c){return"auto"==a&&(a=ms_getTrueLargestSize(c,b,"outerWidth")),a}function in_complementSecondarySize(a,b,c){return"auto"==a&&(a=ms_getTrueLargestSize(c,b,"outerHeight")),a||(a=b.items[b.d.height]),a}function in_getAlignPadding(a,b){var c=cf_getAlignPadding(gi_getCurrentItems(b,a),a);return a.padding[a.d[1]]=c[1],a.padding[a.d[3]]=c[0],a}function in_getResponsiveValues(a,b){var d=cf_getItemAdjustMinMax(Math.ceil(a[a.d.width]/a.items[a.d.width]),a.items.visibleConf);d>b.length&&(d=b.length);var e=Math.floor(a[a.d.width]/d);return a.items.visible=d,a.items[a.d.width]=e,a[a.d.width]=d*e,a}function bt_pauseOnHoverConfig(a){if(is_string(a))var b=a.indexOf("immediate")>-1?!0:!1,c=a.indexOf("resume")>-1?!0:!1;else var b=c=!1;return[b,c]}function bt_mousesheelNumber(a){return is_number(a)?a:null}function is_null(a){return null===a}function is_undefined(a){return is_null(a)||a===void 0||""===a||"undefined"===a}function is_array(a){return a instanceof Array}function is_jquery(a){return a instanceof jQuery}function is_object(a){return(a instanceof Object||"object"==typeof a)&&!is_null(a)&&!is_jquery(a)&&!is_array(a)&&!is_function(a)}function is_number(a){return(a instanceof Number||"number"==typeof a)&&!isNaN(a)}function is_string(a){return(a instanceof String||"string"==typeof a)&&!is_undefined(a)&&!is_true(a)&&!is_false(a)}function is_function(a){return a instanceof Function||"function"==typeof a}function is_boolean(a){return a instanceof Boolean||"boolean"==typeof a||is_true(a)||is_false(a)}function is_true(a){return a===!0||"true"===a}function is_false(a){return a===!1||"false"===a}function is_percentage(a){return is_string(a)&&"%"==a.slice(-1)}function getTime(){return(new Date).getTime()}function deprecated(a,b){debug(!0,a+" is DEPRECATED, support for it will be removed. Use "+b+" instead.")}function debug(a,b){if(!is_undefined(window.console)&&!is_undefined(window.console.log)){if(is_object(a)){var c=" ("+a.selector+")";a=a.debug}else var c="";if(!a)return!1;b=is_string(b)?"carouFredSel"+c+": "+b:["carouFredSel"+c+":",b],window.console.log(b)}return!1}$.fn.carouFredSel||($.fn.caroufredsel=$.fn.carouFredSel=function(options,configs){if(0==this.length)return debug(!0,'No element found for "'+this.selector+'".'),this;if(this.length>1)return this.each(function(){$(this).carouFredSel(options,configs)});var $cfs=this,$tt0=this[0],starting_position=!1;$cfs.data("_cfs_isCarousel")&&(starting_position=$cfs.triggerHandler("_cfs_triggerEvent","currentPosition"),$cfs.trigger("_cfs_triggerEvent",["destroy",!0]));var FN={};FN._init=function(a,b,c){a=go_getObject($tt0,a),a.items=go_getItemsObject($tt0,a.items),a.scroll=go_getScrollObject($tt0,a.scroll),a.auto=go_getAutoObject($tt0,a.auto),a.prev=go_getPrevNextObject($tt0,a.prev),a.next=go_getPrevNextObject($tt0,a.next),a.pagination=go_getPaginationObject($tt0,a.pagination),a.swipe=go_getSwipeObject($tt0,a.swipe),a.mousewheel=go_getMousewheelObject($tt0,a.mousewheel),b&&(opts_orig=$.extend(!0,{},$.fn.carouFredSel.defaults,a)),opts=$.extend(!0,{},$.fn.carouFredSel.defaults,a),opts.d=cf_getDimensions(opts),crsl.direction="up"==opts.direction||"left"==opts.direction?"next":"prev";var d=$cfs.children(),e=ms_getParentSize($wrp,opts,"width");if(is_true(opts.cookie)&&(opts.cookie="caroufredsel_cookie_"+conf.serialNumber),opts.maxDimension=ms_getMaxDimension(opts,e),opts.items=in_complementItems(opts.items,opts,d,c),opts[opts.d.width]=in_complementPrimarySize(opts[opts.d.width],opts,d),opts[opts.d.height]=in_complementSecondarySize(opts[opts.d.height],opts,d),opts.responsive&&(is_percentage(opts[opts.d.width])||(opts[opts.d.width]="100%")),is_percentage(opts[opts.d.width])&&(crsl.upDateOnWindowResize=!0,crsl.primarySizePercentage=opts[opts.d.width],opts[opts.d.width]=ms_getPercentage(e,crsl.primarySizePercentage),opts.items.visible||(opts.items.visibleConf.variable=!0)),opts.responsive?(opts.usePadding=!1,opts.padding=[0,0,0,0],opts.align=!1,opts.items.visibleConf.variable=!1):(opts.items.visible||(opts=in_complementVisibleItems(opts,e)),opts[opts.d.width]||(!opts.items.visibleConf.variable&&is_number(opts.items[opts.d.width])&&"*"==opts.items.filter?(opts[opts.d.width]=opts.items.visible*opts.items[opts.d.width],opts.align=!1):opts[opts.d.width]="variable"),is_undefined(opts.align)&&(opts.align=is_number(opts[opts.d.width])?"center":!1),opts.items.visibleConf.variable&&(opts.items.visible=gn_getVisibleItemsNext(d,opts,0))),"*"==opts.items.filter||opts.items.visibleConf.variable||(opts.items.visibleConf.org=opts.items.visible,opts.items.visible=gn_getVisibleItemsNextFilter(d,opts,0)),opts.items.visible=cf_getItemsAdjust(opts.items.visible,opts,opts.items.visibleConf.adjust,$tt0),opts.items.visibleConf.old=opts.items.visible,opts.responsive)opts.items.visibleConf.min||(opts.items.visibleConf.min=opts.items.visible),opts.items.visibleConf.max||(opts.items.visibleConf.max=opts.items.visible),opts=in_getResponsiveValues(opts,d,e);else switch(opts.padding=cf_getPadding(opts.padding),"top"==opts.align?opts.align="left":"bottom"==opts.align&&(opts.align="right"),opts.align){case"center":case"left":case"right":"variable"!=opts[opts.d.width]&&(opts=in_getAlignPadding(opts,d),opts.usePadding=!0);break;default:opts.align=!1,opts.usePadding=0==opts.padding[0]&&0==opts.padding[1]&&0==opts.padding[2]&&0==opts.padding[3]?!1:!0}is_number(opts.scroll.duration)||(opts.scroll.duration=500),is_undefined(opts.scroll.items)&&(opts.scroll.items=opts.responsive||opts.items.visibleConf.variable||"*"!=opts.items.filter?"visible":opts.items.visible),opts.auto=$.extend(!0,{},opts.scroll,opts.auto),opts.prev=$.extend(!0,{},opts.scroll,opts.prev),opts.next=$.extend(!0,{},opts.scroll,opts.next),opts.pagination=$.extend(!0,{},opts.scroll,opts.pagination),opts.auto=go_complementAutoObject($tt0,opts.auto),opts.prev=go_complementPrevNextObject($tt0,opts.prev),opts.next=go_complementPrevNextObject($tt0,opts.next),opts.pagination=go_complementPaginationObject($tt0,opts.pagination),opts.swipe=go_complementSwipeObject($tt0,opts.swipe),opts.mousewheel=go_complementMousewheelObject($tt0,opts.mousewheel),opts.synchronise&&(opts.synchronise=cf_getSynchArr(opts.synchronise)),opts.auto.onPauseStart&&(opts.auto.onTimeoutStart=opts.auto.onPauseStart,deprecated("auto.onPauseStart","auto.onTimeoutStart")),opts.auto.onPausePause&&(opts.auto.onTimeoutPause=opts.auto.onPausePause,deprecated("auto.onPausePause","auto.onTimeoutPause")),opts.auto.onPauseEnd&&(opts.auto.onTimeoutEnd=opts.auto.onPauseEnd,deprecated("auto.onPauseEnd","auto.onTimeoutEnd")),opts.auto.pauseDuration&&(opts.auto.timeoutDuration=opts.auto.pauseDuration,deprecated("auto.pauseDuration","auto.timeoutDuration"))},FN._build=function(){$cfs.data("_cfs_isCarousel",!0);var a=$cfs.children(),b=in_mapCss($cfs,["textAlign","float","position","top","right","bottom","left","zIndex","width","height","marginTop","marginRight","marginBottom","marginLeft"]),c="relative";switch(b.position){case"absolute":case"fixed":c=b.position}"parent"==conf.wrapper?sz_storeOrigCss($wrp):$wrp.css(b),$wrp.css({overflow:"hidden",position:c}),sz_storeOrigCss($cfs),$cfs.data("_cfs_origCssZindex",b.zIndex),$cfs.css({textAlign:"left","float":"none",position:"absolute",top:0,right:"auto",bottom:"auto",left:0,marginTop:0,marginRight:0,marginBottom:0,marginLeft:0}),sz_storeMargin(a,opts),sz_storeOrigCss(a),opts.responsive&&sz_setResponsiveSizes(opts,a)},FN._bind_events=function(){FN._unbind_events(),$cfs.bind(cf_e("stop",conf),function(a,b){return a.stopPropagation(),crsl.isStopped||opts.auto.button&&opts.auto.button.addClass(cf_c("stopped",conf)),crsl.isStopped=!0,opts.auto.play&&(opts.auto.play=!1,$cfs.trigger(cf_e("pause",conf),b)),!0}),$cfs.bind(cf_e("finish",conf),function(a){return a.stopPropagation(),crsl.isScrolling&&sc_stopScroll(scrl),!0}),$cfs.bind(cf_e("pause",conf),function(a,b,c){if(a.stopPropagation(),tmrs=sc_clearTimers(tmrs),b&&crsl.isScrolling){scrl.isStopped=!0;var d=getTime()-scrl.startTime;scrl.duration-=d,scrl.pre&&(scrl.pre.duration-=d),scrl.post&&(scrl.post.duration-=d),sc_stopScroll(scrl,!1)}if(crsl.isPaused||crsl.isScrolling||c&&(tmrs.timePassed+=getTime()-tmrs.startTime),crsl.isPaused||opts.auto.button&&opts.auto.button.addClass(cf_c("paused",conf)),crsl.isPaused=!0,opts.auto.onTimeoutPause){var e=opts.auto.timeoutDuration-tmrs.timePassed,f=100-Math.ceil(100*e/opts.auto.timeoutDuration);opts.auto.onTimeoutPause.call($tt0,f,e)}return!0}),$cfs.bind(cf_e("play",conf),function(a,b,c,d){a.stopPropagation(),tmrs=sc_clearTimers(tmrs);var e=[b,c,d],f=["string","number","boolean"],g=cf_sortParams(e,f);if(b=g[0],c=g[1],d=g[2],"prev"!=b&&"next"!=b&&(b=crsl.direction),is_number(c)||(c=0),is_boolean(d)||(d=!1),d&&(crsl.isStopped=!1,opts.auto.play=!0),!opts.auto.play)return a.stopImmediatePropagation(),debug(conf,"Carousel stopped: Not scrolling.");crsl.isPaused&&opts.auto.button&&(opts.auto.button.removeClass(cf_c("stopped",conf)),opts.auto.button.removeClass(cf_c("paused",conf))),crsl.isPaused=!1,tmrs.startTime=getTime();var h=opts.auto.timeoutDuration+c;return dur2=h-tmrs.timePassed,perc=100-Math.ceil(100*dur2/h),opts.auto.progress&&(tmrs.progress=setInterval(function(){var a=getTime()-tmrs.startTime+tmrs.timePassed,b=Math.ceil(100*a/h);opts.auto.progress.updater.call(opts.auto.progress.bar[0],b)},opts.auto.progress.interval)),tmrs.auto=setTimeout(function(){opts.auto.progress&&opts.auto.progress.updater.call(opts.auto.progress.bar[0],100),opts.auto.onTimeoutEnd&&opts.auto.onTimeoutEnd.call($tt0,perc,dur2),crsl.isScrolling?$cfs.trigger(cf_e("play",conf),b):$cfs.trigger(cf_e(b,conf),opts.auto)},dur2),opts.auto.onTimeoutStart&&opts.auto.onTimeoutStart.call($tt0,perc,dur2),!0}),$cfs.bind(cf_e("resume",conf),function(a){return a.stopPropagation(),scrl.isStopped?(scrl.isStopped=!1,crsl.isPaused=!1,crsl.isScrolling=!0,scrl.startTime=getTime(),sc_startScroll(scrl,conf)):$cfs.trigger(cf_e("play",conf)),!0}),$cfs.bind(cf_e("prev",conf)+" "+cf_e("next",conf),function(a,b,c,d,e){if(a.stopPropagation(),crsl.isStopped||$cfs.is(":hidden"))return a.stopImmediatePropagation(),debug(conf,"Carousel stopped or hidden: Not scrolling.");var f=is_number(opts.items.minimum)?opts.items.minimum:opts.items.visible+1;if(f>itms.total)return a.stopImmediatePropagation(),debug(conf,"Not enough items ("+itms.total+" total, "+f+" needed): Not scrolling.");var g=[b,c,d,e],h=["object","number/string","function","boolean"],i=cf_sortParams(g,h);b=i[0],c=i[1],d=i[2],e=i[3];var j=a.type.slice(conf.events.prefix.length);if(is_object(b)||(b={}),is_function(d)&&(b.onAfter=d),is_boolean(e)&&(b.queue=e),b=$.extend(!0,{},opts[j],b),b.conditions&&!b.conditions.call($tt0,j))return a.stopImmediatePropagation(),debug(conf,'Callback "conditions" returned false.');if(!is_number(c)){if("*"!=opts.items.filter)c="visible";else for(var k=[c,b.items,opts[j].items],i=0,l=k.length;l>i;i++)if(is_number(k[i])||"page"==k[i]||"visible"==k[i]){c=k[i];break}switch(c){case"page":return a.stopImmediatePropagation(),$cfs.triggerHandler(cf_e(j+"Page",conf),[b,d]);case"visible":opts.items.visibleConf.variable||"*"!=opts.items.filter||(c=opts.items.visible)}}if(scrl.isStopped)return $cfs.trigger(cf_e("resume",conf)),$cfs.trigger(cf_e("queue",conf),[j,[b,c,d]]),a.stopImmediatePropagation(),debug(conf,"Carousel resumed scrolling.");if(b.duration>0&&crsl.isScrolling)return b.queue&&("last"==b.queue&&(queu=[]),("first"!=b.queue||0==queu.length)&&$cfs.trigger(cf_e("queue",conf),[j,[b,c,d]])),a.stopImmediatePropagation(),debug(conf,"Carousel currently scrolling.");if(tmrs.timePassed=0,$cfs.trigger(cf_e("slide_"+j,conf),[b,c]),opts.synchronise)for(var m=opts.synchronise,n=[b,c],o=0,l=m.length;l>o;o++){var p=j;m[o][2]||(p="prev"==p?"next":"prev"),m[o][1]||(n[0]=m[o][0].triggerHandler("_cfs_triggerEvent",["configuration",p])),n[1]=c+m[o][3],m[o][0].trigger("_cfs_triggerEvent",["slide_"+p,n])}return!0}),$cfs.bind(cf_e("slide_prev",conf),function(a,b,c){a.stopPropagation();var d=$cfs.children();if(!opts.circular&&0==itms.first)return opts.infinite&&$cfs.trigger(cf_e("next",conf),itms.total-1),a.stopImmediatePropagation();if(sz_resetMargin(d,opts),!is_number(c)){if(opts.items.visibleConf.variable)c=gn_getVisibleItemsPrev(d,opts,itms.total-1);else if("*"!=opts.items.filter){var e=is_number(b.items)?b.items:gn_getVisibleOrg($cfs,opts);c=gn_getScrollItemsPrevFilter(d,opts,itms.total-1,e)}else c=opts.items.visible;c=cf_getAdjust(c,opts,b.items,$tt0)}if(opts.circular||itms.total-c<itms.first&&(c=itms.total-itms.first),opts.items.visibleConf.old=opts.items.visible,opts.items.visibleConf.variable){var f=cf_getItemsAdjust(gn_getVisibleItemsNext(d,opts,itms.total-c),opts,opts.items.visibleConf.adjust,$tt0);f>=opts.items.visible+c&&itms.total>c&&(c++,f=cf_getItemsAdjust(gn_getVisibleItemsNext(d,opts,itms.total-c),opts,opts.items.visibleConf.adjust,$tt0)),opts.items.visible=f}else if("*"!=opts.items.filter){var f=gn_getVisibleItemsNextFilter(d,opts,itms.total-c);opts.items.visible=cf_getItemsAdjust(f,opts,opts.items.visibleConf.adjust,$tt0)}if(sz_resetMargin(d,opts,!0),0==c)return a.stopImmediatePropagation(),debug(conf,"0 items to scroll: Not scrolling.");for(debug(conf,"Scrolling "+c+" items backward."),itms.first+=c;itms.first>=itms.total;)itms.first-=itms.total;opts.circular||(0==itms.first&&b.onEnd&&b.onEnd.call($tt0,"prev"),opts.infinite||nv_enableNavi(opts,itms.first,conf)),$cfs.children().slice(itms.total-c,itms.total).prependTo($cfs),itms.total<opts.items.visible+c&&$cfs.children().slice(0,opts.items.visible+c-itms.total).clone(!0).appendTo($cfs);var d=$cfs.children(),g=gi_getOldItemsPrev(d,opts,c),h=gi_getNewItemsPrev(d,opts),i=d.eq(c-1),j=g.last(),k=h.last();sz_resetMargin(d,opts);var l=0,m=0;if(opts.align){var n=cf_getAlignPadding(h,opts);l=n[0],m=n[1]}var o=0>l?opts.padding[opts.d[3]]:0,p=!1,q=$();if(c>opts.items.visible&&(q=d.slice(opts.items.visibleConf.old,c),"directscroll"==b.fx)){var r=opts.items[opts.d.width];p=q,i=k,sc_hideHiddenItems(p),opts.items[opts.d.width]="variable"}var s=!1,t=ms_getTotalSize(d.slice(0,c),opts,"width"),u=cf_mapWrapperSizes(ms_getSizes(h,opts,!0),opts,!opts.usePadding),v=0,w={},x={},y={},z={},A={},B={},C={},D=sc_getDuration(b,opts,c,t);switch(b.fx){case"cover":case"cover-fade":v=ms_getTotalSize(d.slice(0,opts.items.visible),opts,"width")}p&&(opts.items[opts.d.width]=r),sz_resetMargin(d,opts,!0),m>=0&&sz_resetMargin(j,opts,opts.padding[opts.d[1]]),l>=0&&sz_resetMargin(i,opts,opts.padding[opts.d[3]]),opts.align&&(opts.padding[opts.d[1]]=m,opts.padding[opts.d[3]]=l),B[opts.d.left]=-(t-o),C[opts.d.left]=-(v-o),x[opts.d.left]=u[opts.d.width];var E=function(){},F=function(){},G=function(){},H=function(){},I=function(){},J=function(){},K=function(){},L=function(){},M=function(){},N=function(){},O=function(){};switch(b.fx){case"crossfade":case"cover":case"cover-fade":case"uncover":case"uncover-fade":s=$cfs.clone(!0).appendTo($wrp)}switch(b.fx){case"crossfade":case"uncover":case"uncover-fade":s.children().slice(0,c).remove(),s.children().slice(opts.items.visibleConf.old).remove();break;case"cover":case"cover-fade":s.children().slice(opts.items.visible).remove(),s.css(C)}if($cfs.css(B),scrl=sc_setScroll(D,b.easing,conf),w[opts.d.left]=opts.usePadding?opts.padding[opts.d[3]]:0,("variable"==opts[opts.d.width]||"variable"==opts[opts.d.height])&&(E=function(){$wrp.css(u)},F=function(){scrl.anims.push([$wrp,u])}),opts.usePadding){switch(k.not(i).length&&(y[opts.d.marginRight]=i.data("_cfs_origCssMargin"),0>l?i.css(y):(K=function(){i.css(y)},L=function(){scrl.anims.push([i,y])})),b.fx){case"cover":case"cover-fade":s.children().eq(c-1).css(y)}k.not(j).length&&(z[opts.d.marginRight]=j.data("_cfs_origCssMargin"),G=function(){j.css(z)},H=function(){scrl.anims.push([j,z])}),m>=0&&(A[opts.d.marginRight]=k.data("_cfs_origCssMargin")+opts.padding[opts.d[1]],I=function(){k.css(A)},J=function(){scrl.anims.push([k,A])})}O=function(){$cfs.css(w)};var P=opts.items.visible+c-itms.total;N=function(){if(P>0&&($cfs.children().slice(itms.total).remove(),g=$($cfs.children().slice(itms.total-(opts.items.visible-P)).get().concat($cfs.children().slice(0,P).get()))),sc_showHiddenItems(p),opts.usePadding){var a=$cfs.children().eq(opts.items.visible+c-1);a.css(opts.d.marginRight,a.data("_cfs_origCssMargin"))}};var Q=sc_mapCallbackArguments(g,q,h,c,"prev",D,u);switch(M=function(){sc_afterScroll($cfs,s,b),crsl.isScrolling=!1,clbk.onAfter=sc_fireCallbacks($tt0,b,"onAfter",Q,clbk),queu=sc_fireQueue($cfs,queu,conf),crsl.isPaused||$cfs.trigger(cf_e("play",conf))},crsl.isScrolling=!0,tmrs=sc_clearTimers(tmrs),clbk.onBefore=sc_fireCallbacks($tt0,b,"onBefore",Q,clbk),b.fx){case"none":$cfs.css(w),E(),G(),I(),K(),O(),N(),M();break;case"fade":scrl.anims.push([$cfs,{opacity:0},function(){E(),G(),I(),K(),O(),N(),scrl=sc_setScroll(D,b.easing,conf),scrl.anims.push([$cfs,{opacity:1},M]),sc_startScroll(scrl,conf)}]);break;case"crossfade":$cfs.css({opacity:0}),scrl.anims.push([s,{opacity:0}]),scrl.anims.push([$cfs,{opacity:1},M]),F(),G(),I(),K(),O(),N();break;case"cover":scrl.anims.push([s,w,function(){G(),I(),K(),O(),N(),M()}]),F();break;case"cover-fade":scrl.anims.push([$cfs,{opacity:0}]),scrl.anims.push([s,w,function(){G(),I(),K(),O(),N(),M()}]),F();break;case"uncover":scrl.anims.push([s,x,M]),F(),G(),I(),K(),O(),N();break;case"uncover-fade":$cfs.css({opacity:0}),scrl.anims.push([$cfs,{opacity:1}]),scrl.anims.push([s,x,M]),F(),G(),I(),K(),O(),N();break;default:scrl.anims.push([$cfs,w,function(){N(),M()}]),F(),H(),J(),L()}return sc_startScroll(scrl,conf),cf_setCookie(opts.cookie,$cfs,conf),$cfs.trigger(cf_e("updatePageStatus",conf),[!1,u]),!0
}),$cfs.bind(cf_e("slide_next",conf),function(a,b,c){a.stopPropagation();var d=$cfs.children();if(!opts.circular&&itms.first==opts.items.visible)return opts.infinite&&$cfs.trigger(cf_e("prev",conf),itms.total-1),a.stopImmediatePropagation();if(sz_resetMargin(d,opts),!is_number(c)){if("*"!=opts.items.filter){var e=is_number(b.items)?b.items:gn_getVisibleOrg($cfs,opts);c=gn_getScrollItemsNextFilter(d,opts,0,e)}else c=opts.items.visible;c=cf_getAdjust(c,opts,b.items,$tt0)}var f=0==itms.first?itms.total:itms.first;if(!opts.circular){if(opts.items.visibleConf.variable)var g=gn_getVisibleItemsNext(d,opts,c),e=gn_getVisibleItemsPrev(d,opts,f-1);else var g=opts.items.visible,e=opts.items.visible;c+g>f&&(c=f-e)}if(opts.items.visibleConf.old=opts.items.visible,opts.items.visibleConf.variable){for(var g=cf_getItemsAdjust(gn_getVisibleItemsNextTestCircular(d,opts,c,f),opts,opts.items.visibleConf.adjust,$tt0);opts.items.visible-c>=g&&itms.total>c;)c++,g=cf_getItemsAdjust(gn_getVisibleItemsNextTestCircular(d,opts,c,f),opts,opts.items.visibleConf.adjust,$tt0);opts.items.visible=g}else if("*"!=opts.items.filter){var g=gn_getVisibleItemsNextFilter(d,opts,c);opts.items.visible=cf_getItemsAdjust(g,opts,opts.items.visibleConf.adjust,$tt0)}if(sz_resetMargin(d,opts,!0),0==c)return a.stopImmediatePropagation(),debug(conf,"0 items to scroll: Not scrolling.");for(debug(conf,"Scrolling "+c+" items forward."),itms.first-=c;0>itms.first;)itms.first+=itms.total;opts.circular||(itms.first==opts.items.visible&&b.onEnd&&b.onEnd.call($tt0,"next"),opts.infinite||nv_enableNavi(opts,itms.first,conf)),itms.total<opts.items.visible+c&&$cfs.children().slice(0,opts.items.visible+c-itms.total).clone(!0).appendTo($cfs);var d=$cfs.children(),h=gi_getOldItemsNext(d,opts),i=gi_getNewItemsNext(d,opts,c),j=d.eq(c-1),k=h.last(),l=i.last();sz_resetMargin(d,opts);var m=0,n=0;if(opts.align){var o=cf_getAlignPadding(i,opts);m=o[0],n=o[1]}var p=!1,q=$();if(c>opts.items.visibleConf.old&&(q=d.slice(opts.items.visibleConf.old,c),"directscroll"==b.fx)){var r=opts.items[opts.d.width];p=q,j=k,sc_hideHiddenItems(p),opts.items[opts.d.width]="variable"}var s=!1,t=ms_getTotalSize(d.slice(0,c),opts,"width"),u=cf_mapWrapperSizes(ms_getSizes(i,opts,!0),opts,!opts.usePadding),v=0,w={},x={},y={},z={},A={},B=sc_getDuration(b,opts,c,t);switch(b.fx){case"uncover":case"uncover-fade":v=ms_getTotalSize(d.slice(0,opts.items.visibleConf.old),opts,"width")}p&&(opts.items[opts.d.width]=r),opts.align&&0>opts.padding[opts.d[1]]&&(opts.padding[opts.d[1]]=0),sz_resetMargin(d,opts,!0),sz_resetMargin(k,opts,opts.padding[opts.d[1]]),opts.align&&(opts.padding[opts.d[1]]=n,opts.padding[opts.d[3]]=m),A[opts.d.left]=opts.usePadding?opts.padding[opts.d[3]]:0;var C=function(){},D=function(){},E=function(){},F=function(){},G=function(){},H=function(){},I=function(){},J=function(){},K=function(){};switch(b.fx){case"crossfade":case"cover":case"cover-fade":case"uncover":case"uncover-fade":s=$cfs.clone(!0).appendTo($wrp),s.children().slice(opts.items.visibleConf.old).remove()}switch(b.fx){case"crossfade":case"cover":case"cover-fade":$cfs.css("zIndex",1),s.css("zIndex",0)}if(scrl=sc_setScroll(B,b.easing,conf),w[opts.d.left]=-t,x[opts.d.left]=-v,0>m&&(w[opts.d.left]+=m),("variable"==opts[opts.d.width]||"variable"==opts[opts.d.height])&&(C=function(){$wrp.css(u)},D=function(){scrl.anims.push([$wrp,u])}),opts.usePadding){var L=l.data("_cfs_origCssMargin");n>=0&&(L+=opts.padding[opts.d[1]]),l.css(opts.d.marginRight,L),j.not(k).length&&(z[opts.d.marginRight]=k.data("_cfs_origCssMargin")),E=function(){k.css(z)},F=function(){scrl.anims.push([k,z])};var M=j.data("_cfs_origCssMargin");m>0&&(M+=opts.padding[opts.d[3]]),y[opts.d.marginRight]=M,G=function(){j.css(y)},H=function(){scrl.anims.push([j,y])}}K=function(){$cfs.css(A)};var N=opts.items.visible+c-itms.total;J=function(){N>0&&$cfs.children().slice(itms.total).remove();var a=$cfs.children().slice(0,c).appendTo($cfs).last();if(N>0&&(i=gi_getCurrentItems(d,opts)),sc_showHiddenItems(p),opts.usePadding){if(itms.total<opts.items.visible+c){var b=$cfs.children().eq(opts.items.visible-1);b.css(opts.d.marginRight,b.data("_cfs_origCssMargin")+opts.padding[opts.d[1]])}a.css(opts.d.marginRight,a.data("_cfs_origCssMargin"))}};var O=sc_mapCallbackArguments(h,q,i,c,"next",B,u);switch(I=function(){$cfs.css("zIndex",$cfs.data("_cfs_origCssZindex")),sc_afterScroll($cfs,s,b),crsl.isScrolling=!1,clbk.onAfter=sc_fireCallbacks($tt0,b,"onAfter",O,clbk),queu=sc_fireQueue($cfs,queu,conf),crsl.isPaused||$cfs.trigger(cf_e("play",conf))},crsl.isScrolling=!0,tmrs=sc_clearTimers(tmrs),clbk.onBefore=sc_fireCallbacks($tt0,b,"onBefore",O,clbk),b.fx){case"none":$cfs.css(w),C(),E(),G(),K(),J(),I();break;case"fade":scrl.anims.push([$cfs,{opacity:0},function(){C(),E(),G(),K(),J(),scrl=sc_setScroll(B,b.easing,conf),scrl.anims.push([$cfs,{opacity:1},I]),sc_startScroll(scrl,conf)}]);break;case"crossfade":$cfs.css({opacity:0}),scrl.anims.push([s,{opacity:0}]),scrl.anims.push([$cfs,{opacity:1},I]),D(),E(),G(),K(),J();break;case"cover":$cfs.css(opts.d.left,$wrp[opts.d.width]()),scrl.anims.push([$cfs,A,I]),D(),E(),G(),J();break;case"cover-fade":$cfs.css(opts.d.left,$wrp[opts.d.width]()),scrl.anims.push([s,{opacity:0}]),scrl.anims.push([$cfs,A,I]),D(),E(),G(),J();break;case"uncover":scrl.anims.push([s,x,I]),D(),E(),G(),K(),J();break;case"uncover-fade":$cfs.css({opacity:0}),scrl.anims.push([$cfs,{opacity:1}]),scrl.anims.push([s,x,I]),D(),E(),G(),K(),J();break;default:scrl.anims.push([$cfs,w,function(){K(),J(),I()}]),D(),F(),H()}return sc_startScroll(scrl,conf),cf_setCookie(opts.cookie,$cfs,conf),$cfs.trigger(cf_e("updatePageStatus",conf),[!1,u]),!0}),$cfs.bind(cf_e("slideTo",conf),function(a,b,c,d,e,f,g){a.stopPropagation();var h=[b,c,d,e,f,g],i=["string/number/object","number","boolean","object","string","function"],j=cf_sortParams(h,i);return e=j[3],f=j[4],g=j[5],b=gn_getItemIndex(j[0],j[1],j[2],itms,$cfs),0==b?!1:(is_object(e)||(e=!1),"prev"!=f&&"next"!=f&&(f=opts.circular?itms.total/2>=b?"next":"prev":0==itms.first||itms.first>b?"next":"prev"),"prev"==f&&(b=itms.total-b),$cfs.trigger(cf_e(f,conf),[e,b,g]),!0)}),$cfs.bind(cf_e("prevPage",conf),function(a,b,c){a.stopPropagation();var d=$cfs.triggerHandler(cf_e("currentPage",conf));return $cfs.triggerHandler(cf_e("slideToPage",conf),[d-1,b,"prev",c])}),$cfs.bind(cf_e("nextPage",conf),function(a,b,c){a.stopPropagation();var d=$cfs.triggerHandler(cf_e("currentPage",conf));return $cfs.triggerHandler(cf_e("slideToPage",conf),[d+1,b,"next",c])}),$cfs.bind(cf_e("slideToPage",conf),function(a,b,c,d,e){a.stopPropagation(),is_number(b)||(b=$cfs.triggerHandler(cf_e("currentPage",conf)));var f=opts.pagination.items||opts.items.visible,g=Math.ceil(itms.total/f)-1;return 0>b&&(b=g),b>g&&(b=0),$cfs.triggerHandler(cf_e("slideTo",conf),[b*f,0,!0,c,d,e])}),$cfs.bind(cf_e("jumpToStart",conf),function(a,b){if(a.stopPropagation(),b=b?gn_getItemIndex(b,0,!0,itms,$cfs):0,b+=itms.first,0!=b){if(itms.total>0)for(;b>itms.total;)b-=itms.total;$cfs.prepend($cfs.children().slice(b,itms.total))}return!0}),$cfs.bind(cf_e("synchronise",conf),function(a,b){if(a.stopPropagation(),b)b=cf_getSynchArr(b);else{if(!opts.synchronise)return debug(conf,"No carousel to synchronise.");b=opts.synchronise}for(var c=$cfs.triggerHandler(cf_e("currentPosition",conf)),d=!0,e=0,f=b.length;f>e;e++)b[e][0].triggerHandler(cf_e("slideTo",conf),[c,b[e][3],!0])||(d=!1);return d}),$cfs.bind(cf_e("queue",conf),function(a,b,c){return a.stopPropagation(),is_function(b)?b.call($tt0,queu):is_array(b)?queu=b:is_undefined(b)||queu.push([b,c]),queu}),$cfs.bind(cf_e("insertItem",conf),function(a,b,c,d,e){a.stopPropagation();var f=[b,c,d,e],g=["string/object","string/number/object","boolean","number"],h=cf_sortParams(f,g);if(b=h[0],c=h[1],d=h[2],e=h[3],is_object(b)&&!is_jquery(b)?b=$(b):is_string(b)&&(b=$(b)),!is_jquery(b)||0==b.length)return debug(conf,"Not a valid object.");is_undefined(c)&&(c="end"),sz_storeMargin(b,opts),sz_storeOrigCss(b);var i=c,j="before";"end"==c?d?(0==itms.first?(c=itms.total-1,j="after"):(c=itms.first,itms.first+=b.length),0>c&&(c=0)):(c=itms.total-1,j="after"):c=gn_getItemIndex(c,e,d,itms,$cfs);var k=$cfs.children().eq(c);return k.length?k[j](b):(debug(conf,"Correct insert-position not found! Appending item to the end."),$cfs.append(b)),"end"==i||d||itms.first>c&&(itms.first+=b.length),itms.total=$cfs.children().length,itms.first>=itms.total&&(itms.first-=itms.total),$cfs.trigger(cf_e("updateSizes",conf)),$cfs.trigger(cf_e("linkAnchors",conf)),!0}),$cfs.bind(cf_e("removeItem",conf),function(a,b,c,d){a.stopPropagation();var e=[b,c,d],f=["string/number/object","boolean","number"],g=cf_sortParams(e,f);if(b=g[0],c=g[1],d=g[2],b instanceof $&&b.length>1)return i=$(),b.each(function(){var e=$cfs.trigger(cf_e("removeItem",conf),[$(this),c,d]);e&&(i=i.add(e))}),i;if(is_undefined(b)||"end"==b)i=$cfs.children().last();else{b=gn_getItemIndex(b,d,c,itms,$cfs);var i=$cfs.children().eq(b);i.length&&itms.first>b&&(itms.first-=i.length)}return i&&i.length&&(i.detach(),itms.total=$cfs.children().length,$cfs.trigger(cf_e("updateSizes",conf))),i}),$cfs.bind(cf_e("onBefore",conf)+" "+cf_e("onAfter",conf),function(a,b){a.stopPropagation();var c=a.type.slice(conf.events.prefix.length);return is_array(b)&&(clbk[c]=b),is_function(b)&&clbk[c].push(b),clbk[c]}),$cfs.bind(cf_e("currentPosition",conf),function(a,b){if(a.stopPropagation(),0==itms.first)var c=0;else var c=itms.total-itms.first;return is_function(b)&&b.call($tt0,c),c}),$cfs.bind(cf_e("currentPage",conf),function(a,b){a.stopPropagation();var e,c=opts.pagination.items||opts.items.visible,d=Math.ceil(itms.total/c-1);return e=0==itms.first?0:itms.first<itms.total%c?0:itms.first!=c||opts.circular?Math.round((itms.total-itms.first)/c):d,0>e&&(e=0),e>d&&(e=d),is_function(b)&&b.call($tt0,e),e}),$cfs.bind(cf_e("currentVisible",conf),function(a,b){a.stopPropagation();var c=gi_getCurrentItems($cfs.children(),opts);return is_function(b)&&b.call($tt0,c),c}),$cfs.bind(cf_e("slice",conf),function(a,b,c,d){if(a.stopPropagation(),0==itms.total)return!1;var e=[b,c,d],f=["number","number","function"],g=cf_sortParams(e,f);if(b=is_number(g[0])?g[0]:0,c=is_number(g[1])?g[1]:itms.total,d=g[2],b+=itms.first,c+=itms.first,items.total>0){for(;b>itms.total;)b-=itms.total;for(;c>itms.total;)c-=itms.total;for(;0>b;)b+=itms.total;for(;0>c;)c+=itms.total}var i,h=$cfs.children();return i=c>b?h.slice(b,c):$(h.slice(b,itms.total).get().concat(h.slice(0,c).get())),is_function(d)&&d.call($tt0,i),i}),$cfs.bind(cf_e("isPaused",conf)+" "+cf_e("isStopped",conf)+" "+cf_e("isScrolling",conf),function(a,b){a.stopPropagation();var c=a.type.slice(conf.events.prefix.length),d=crsl[c];return is_function(b)&&b.call($tt0,d),d}),$cfs.bind(cf_e("configuration",conf),function(e,a,b,c){e.stopPropagation();var reInit=!1;if(is_function(a))a.call($tt0,opts);else if(is_object(a))opts_orig=$.extend(!0,{},opts_orig,a),b!==!1?reInit=!0:opts=$.extend(!0,{},opts,a);else if(!is_undefined(a))if(is_function(b)){var val=eval("opts."+a);is_undefined(val)&&(val=""),b.call($tt0,val)}else{if(is_undefined(b))return eval("opts."+a);"boolean"!=typeof c&&(c=!0),eval("opts_orig."+a+" = b"),c!==!1?reInit=!0:eval("opts."+a+" = b")}if(reInit){sz_resetMargin($cfs.children(),opts),FN._init(opts_orig),FN._bind_buttons();var sz=sz_setSizes($cfs,opts);$cfs.trigger(cf_e("updatePageStatus",conf),[!0,sz])}return opts}),$cfs.bind(cf_e("linkAnchors",conf),function(a,b,c){return a.stopPropagation(),is_undefined(b)?b=$("body"):is_string(b)&&(b=$(b)),is_jquery(b)&&0!=b.length?(is_string(c)||(c="a.caroufredsel"),b.find(c).each(function(){var a=this.hash||"";a.length>0&&-1!=$cfs.children().index($(a))&&$(this).unbind("click").click(function(b){b.preventDefault(),$cfs.trigger(cf_e("slideTo",conf),a)})}),!0):debug(conf,"Not a valid object.")}),$cfs.bind(cf_e("updatePageStatus",conf),function(a,b){if(a.stopPropagation(),opts.pagination.container){var d=opts.pagination.items||opts.items.visible,e=Math.ceil(itms.total/d);b&&(opts.pagination.anchorBuilder&&(opts.pagination.container.children().remove(),opts.pagination.container.each(function(){for(var a=0;e>a;a++){var b=$cfs.children().eq(gn_getItemIndex(a*d,0,!0,itms,$cfs));$(this).append(opts.pagination.anchorBuilder.call(b[0],a+1))}})),opts.pagination.container.each(function(){$(this).children().unbind(opts.pagination.event).each(function(a){$(this).bind(opts.pagination.event,function(b){b.preventDefault(),$cfs.trigger(cf_e("slideTo",conf),[a*d,-opts.pagination.deviation,!0,opts.pagination])})})}));var f=$cfs.triggerHandler(cf_e("currentPage",conf))+opts.pagination.deviation;return f>=e&&(f=0),0>f&&(f=e-1),opts.pagination.container.each(function(){$(this).children().removeClass(cf_c("selected",conf)).eq(f).addClass(cf_c("selected",conf))}),!0}}),$cfs.bind(cf_e("updateSizes",conf),function(){var b=opts.items.visible,c=$cfs.children(),d=ms_getParentSize($wrp,opts,"width");if(itms.total=c.length,crsl.primarySizePercentage?(opts.maxDimension=d,opts[opts.d.width]=ms_getPercentage(d,crsl.primarySizePercentage)):opts.maxDimension=ms_getMaxDimension(opts,d),opts.responsive?(opts.items.width=opts.items.sizesConf.width,opts.items.height=opts.items.sizesConf.height,opts=in_getResponsiveValues(opts,c,d),b=opts.items.visible,sz_setResponsiveSizes(opts,c)):opts.items.visibleConf.variable?b=gn_getVisibleItemsNext(c,opts,0):"*"!=opts.items.filter&&(b=gn_getVisibleItemsNextFilter(c,opts,0)),!opts.circular&&0!=itms.first&&b>itms.first){if(opts.items.visibleConf.variable)var e=gn_getVisibleItemsPrev(c,opts,itms.first)-itms.first;else if("*"!=opts.items.filter)var e=gn_getVisibleItemsPrevFilter(c,opts,itms.first)-itms.first;else var e=opts.items.visible-itms.first;debug(conf,"Preventing non-circular: sliding "+e+" items backward."),$cfs.trigger(cf_e("prev",conf),e)}opts.items.visible=cf_getItemsAdjust(b,opts,opts.items.visibleConf.adjust,$tt0),opts.items.visibleConf.old=opts.items.visible,opts=in_getAlignPadding(opts,c);var f=sz_setSizes($cfs,opts);return $cfs.trigger(cf_e("updatePageStatus",conf),[!0,f]),nv_showNavi(opts,itms.total,conf),nv_enableNavi(opts,itms.first,conf),f}),$cfs.bind(cf_e("destroy",conf),function(a,b){return a.stopPropagation(),tmrs=sc_clearTimers(tmrs),$cfs.data("_cfs_isCarousel",!1),$cfs.trigger(cf_e("finish",conf)),b&&$cfs.trigger(cf_e("jumpToStart",conf)),sz_restoreOrigCss($cfs.children()),sz_restoreOrigCss($cfs),FN._unbind_events(),FN._unbind_buttons(),"parent"==conf.wrapper?sz_restoreOrigCss($wrp):$wrp.replaceWith($cfs),!0}),$cfs.bind(cf_e("debug",conf),function(){return debug(conf,"Carousel width: "+opts.width),debug(conf,"Carousel height: "+opts.height),debug(conf,"Item widths: "+opts.items.width),debug(conf,"Item heights: "+opts.items.height),debug(conf,"Number of items visible: "+opts.items.visible),opts.auto.play&&debug(conf,"Number of items scrolled automatically: "+opts.auto.items),opts.prev.button&&debug(conf,"Number of items scrolled backward: "+opts.prev.items),opts.next.button&&debug(conf,"Number of items scrolled forward: "+opts.next.items),conf.debug}),$cfs.bind("_cfs_triggerEvent",function(a,b,c){return a.stopPropagation(),$cfs.triggerHandler(cf_e(b,conf),c)})},FN._unbind_events=function(){$cfs.unbind(cf_e("",conf)),$cfs.unbind(cf_e("",conf,!1)),$cfs.unbind("_cfs_triggerEvent")},FN._bind_buttons=function(){if(FN._unbind_buttons(),nv_showNavi(opts,itms.total,conf),nv_enableNavi(opts,itms.first,conf),opts.auto.pauseOnHover){var a=bt_pauseOnHoverConfig(opts.auto.pauseOnHover);$wrp.bind(cf_e("mouseenter",conf,!1),function(){$cfs.trigger(cf_e("pause",conf),a)}).bind(cf_e("mouseleave",conf,!1),function(){$cfs.trigger(cf_e("resume",conf))})}if(opts.auto.button&&opts.auto.button.bind(cf_e(opts.auto.event,conf,!1),function(a){a.preventDefault();var b=!1,c=null;crsl.isPaused?b="play":opts.auto.pauseOnEvent&&(b="pause",c=bt_pauseOnHoverConfig(opts.auto.pauseOnEvent)),b&&$cfs.trigger(cf_e(b,conf),c)}),opts.prev.button&&(opts.prev.button.bind(cf_e(opts.prev.event,conf,!1),function(a){a.preventDefault(),$cfs.trigger(cf_e("prev",conf))}),opts.prev.pauseOnHover)){var a=bt_pauseOnHoverConfig(opts.prev.pauseOnHover);opts.prev.button.bind(cf_e("mouseenter",conf,!1),function(){$cfs.trigger(cf_e("pause",conf),a)}).bind(cf_e("mouseleave",conf,!1),function(){$cfs.trigger(cf_e("resume",conf))})}if(opts.next.button&&(opts.next.button.bind(cf_e(opts.next.event,conf,!1),function(a){a.preventDefault(),$cfs.trigger(cf_e("next",conf))}),opts.next.pauseOnHover)){var a=bt_pauseOnHoverConfig(opts.next.pauseOnHover);opts.next.button.bind(cf_e("mouseenter",conf,!1),function(){$cfs.trigger(cf_e("pause",conf),a)}).bind(cf_e("mouseleave",conf,!1),function(){$cfs.trigger(cf_e("resume",conf))})}if(opts.pagination.container&&opts.pagination.pauseOnHover){var a=bt_pauseOnHoverConfig(opts.pagination.pauseOnHover);opts.pagination.container.bind(cf_e("mouseenter",conf,!1),function(){$cfs.trigger(cf_e("pause",conf),a)}).bind(cf_e("mouseleave",conf,!1),function(){$cfs.trigger(cf_e("resume",conf))})}if((opts.prev.key||opts.next.key)&&$(document).bind(cf_e("keyup",conf,!1,!0,!0),function(a){var b=a.keyCode;b==opts.next.key&&(a.preventDefault(),$cfs.trigger(cf_e("next",conf))),b==opts.prev.key&&(a.preventDefault(),$cfs.trigger(cf_e("prev",conf)))}),opts.pagination.keys&&$(document).bind(cf_e("keyup",conf,!1,!0,!0),function(a){var b=a.keyCode;b>=49&&58>b&&(b=(b-49)*opts.items.visible,itms.total>=b&&(a.preventDefault(),$cfs.trigger(cf_e("slideTo",conf),[b,0,!0,opts.pagination])))}),$.fn.swipe){var b="ontouchstart"in window;if(b&&opts.swipe.onTouch||!b&&opts.swipe.onMouse){var c=$.extend(!0,{},opts.prev,opts.swipe),d=$.extend(!0,{},opts.next,opts.swipe),e=function(){$cfs.trigger(cf_e("prev",conf),[c])},f=function(){$cfs.trigger(cf_e("next",conf),[d])};switch(opts.direction){case"up":case"down":opts.swipe.options.swipeUp=f,opts.swipe.options.swipeDown=e;break;default:opts.swipe.options.swipeLeft=f,opts.swipe.options.swipeRight=e}crsl.swipe&&$cfs.swipe("destroy"),$wrp.swipe(opts.swipe.options),$wrp.css("cursor","move"),crsl.swipe=!0}}if($.fn.mousewheel&&opts.mousewheel){var g=$.extend(!0,{},opts.prev,opts.mousewheel),h=$.extend(!0,{},opts.next,opts.mousewheel);crsl.mousewheel&&$wrp.unbind(cf_e("mousewheel",conf,!1)),$wrp.bind(cf_e("mousewheel",conf,!1),function(a,b){a.preventDefault(),b>0?$cfs.trigger(cf_e("prev",conf),[g]):$cfs.trigger(cf_e("next",conf),[h])}),crsl.mousewheel=!0}if(opts.auto.play&&$cfs.trigger(cf_e("play",conf),opts.auto.delay),crsl.upDateOnWindowResize){var i=function(){$cfs.trigger(cf_e("finish",conf)),opts.auto.pauseOnResize&&!crsl.isPaused&&$cfs.trigger(cf_e("play",conf)),sz_resetMargin($cfs.children(),opts),$cfs.trigger(cf_e("updateSizes",conf))},j=$(window),k=null;if($.debounce&&"debounce"==conf.onWindowResize)k=$.debounce(200,i);else if($.throttle&&"throttle"==conf.onWindowResize)k=$.throttle(300,i);else{var l=0,m=0;k=function(){var a=j.width(),b=j.height();(a!=l||b!=m)&&(i(),l=a,m=b)}}j.bind(cf_e("resize",conf,!1,!0,!0),k)}},FN._unbind_buttons=function(){var b=(cf_e("",conf),cf_e("",conf,!1));ns3=cf_e("",conf,!1,!0,!0),$(document).unbind(ns3),$(window).unbind(ns3),$wrp.unbind(b),opts.auto.button&&opts.auto.button.unbind(b),opts.prev.button&&opts.prev.button.unbind(b),opts.next.button&&opts.next.button.unbind(b),opts.pagination.container&&(opts.pagination.container.unbind(b),opts.pagination.anchorBuilder&&opts.pagination.container.children().remove()),crsl.swipe&&($cfs.swipe("destroy"),$wrp.css("cursor","default"),crsl.swipe=!1),crsl.mousewheel&&(crsl.mousewheel=!1),nv_showNavi(opts,"hide",conf),nv_enableNavi(opts,"removeClass",conf)},is_boolean(configs)&&(configs={debug:configs});var crsl={direction:"next",isPaused:!0,isScrolling:!1,isStopped:!1,mousewheel:!1,swipe:!1},itms={total:$cfs.children().length,first:0},tmrs={auto:null,progress:null,startTime:getTime(),timePassed:0},scrl={isStopped:!1,duration:0,startTime:0,easing:"",anims:[]},clbk={onBefore:[],onAfter:[]},queu=[],conf=$.extend(!0,{},$.fn.carouFredSel.configs,configs),opts={},opts_orig=$.extend(!0,{},options),$wrp="parent"==conf.wrapper?$cfs.parent():$cfs.wrap("<"+conf.wrapper.element+' class="'+conf.wrapper.classname+'" />').parent();if(conf.selector=$cfs.selector,conf.serialNumber=$.fn.carouFredSel.serialNumber++,conf.transition=conf.transition&&$.fn.transition?"transition":"animate",FN._init(opts_orig,!0,starting_position),FN._build(),FN._bind_events(),FN._bind_buttons(),is_array(opts.items.start))var start_arr=opts.items.start;else{var start_arr=[];0!=opts.items.start&&start_arr.push(opts.items.start)}if(opts.cookie&&start_arr.unshift(parseInt(cf_getCookie(opts.cookie),10)),start_arr.length>0)for(var a=0,l=start_arr.length;l>a;a++){var s=start_arr[a];if(0!=s){if(s===!0){if(s=window.location.hash,1>s.length)continue}else"random"===s&&(s=Math.floor(Math.random()*itms.total));if($cfs.triggerHandler(cf_e("slideTo",conf),[s,0,!0,{fx:"none"}]))break}}var siz=sz_setSizes($cfs,opts),itm=gi_getCurrentItems($cfs.children(),opts);return opts.onCreate&&opts.onCreate.call($tt0,{width:siz.width,height:siz.height,items:itm}),$cfs.trigger(cf_e("updatePageStatus",conf),[!0,siz]),$cfs.trigger(cf_e("linkAnchors",conf)),conf.debug&&$cfs.trigger(cf_e("debug",conf)),$cfs},$.fn.carouFredSel.serialNumber=1,$.fn.carouFredSel.defaults={synchronise:!1,infinite:!0,circular:!0,responsive:!1,direction:"left",items:{start:0},scroll:{easing:"swing",duration:500,pauseOnHover:!1,event:"click",queue:!1}},$.fn.carouFredSel.configs={debug:!1,transition:!1,onWindowResize:"throttle",events:{prefix:"",namespace:"cfs"},wrapper:{element:"div",classname:"caroufredsel_wrapper"},classnames:{}},$.fn.carouFredSel.pageAnchorBuilder=function(a){return'<a href="#"><span>'+a+"</span></a>"},$.fn.carouFredSel.progressbarUpdater=function(a){$(this).css("width",a+"%")},$.fn.carouFredSel.cookie={get:function(a){a+="=";for(var b=document.cookie.split(";"),c=0,d=b.length;d>c;c++){for(var e=b[c];" "==e.charAt(0);)e=e.slice(1);if(0==e.indexOf(a))return e.slice(a.length)}return 0},set:function(a,b,c){var d="";if(c){var e=new Date;e.setTime(e.getTime()+1e3*60*60*24*c),d="; expires="+e.toGMTString()}document.cookie=a+"="+b+d+"; path=/"},remove:function(a){$.fn.carouFredSel.cookie.set(a,"",-1)}},$.extend($.easing,{quadratic:function(a){var b=a*a;return a*(-b*a+4*b-6*a+4)},cubic:function(a){return a*(4*a*a-9*a+6)},elastic:function(a){var b=a*a;return a*(33*b*b-106*b*a+126*b-67*a+15)}}))})(jQuery);
/*------------------------------------------------------------------------------------------------------
 Responsive Dropdowns
 --------------------------------------------------------------------------------------------------------*/

var Selects = {
    $: jQuery,
    $selects: false,
    // On mobile, we add a better select box. This function
    // populates data from the <select> element to it's
    // <label> element which is positioned over the select box.
    populate_select_label: function ( is_mobile ) {
        // Abort when no select elements are found
        if ( !this.$selects || !this.$selects.length ) {
            return;
        }

        // Handle small screens
        //				if ( is_mobile ) {

        this.$selects.each( function ( idx, val ) {
            var $select = $( this ),
                data = $select.data( 'buddyboss-select-info' ),
                $label;

            if ( !data || !data.$label && $select.data( 'state' ) !== 'pass' ) {
                return;
            }

            $label = data.$label;

            if ( $label && $label.length ) {

                $select.data( 'state', 'mobile' );

                $label.text( $select.find( 'option:selected' ).text() ).show();
            }
        } );

    }, // end populate_select_label();


    // On page load we'll go through each select element and make sure
    // we have a label element to accompany it. If not, we'll generate
    // one and add it dynamically.

    // On page load we'll go through each select element and make sure
    // we have a label element to accompany it. If not, we'll generate
    // one and add it dynamically.
    init_select: function ( is_mobile, mode ) {
        var current = 0,
            that = this;

        if ( !mode ) {
            //only few buddypress and bbpress related fields
            this.$selects = $( '.item-list-tabs select, #whats-new-form select, .editfield select, #notifications-bulk-management select, #messages-bulk-management select, .field-visibility select, .register-section select, .bbp-form select, #bp-group-course, #bbp_group_forum_id' );
        } else {
            //all fields
            this.$selects = $( '#page select:not([multiple]):not(#bbwall-privacy-selectbox):not(.bp-ap-selectbox)' ).filter( function () {
                return ( !$( this ).closest( '.frm_form_field' ).length );
            } );
        }

        this.$selects.each( function () {
            var $select = $( this ),
                $wrap, id, $span, $label, dynamic = false, state = 'pass';

            if ( !( $select.data( 'state' ) && $select.data( 'state' ) === 'mobile' ) ) {

                if ( this.style.display === 'none' ||  $select.hasClass('select2-hidden-accessible') ) {
                    return;
                }

                $wrap = $( '<div class="buddyboss-select"></div>' );

                if ( $( this ).hasClass( 'large' ) ) {
                    $wrap.addClass( 'large' );
                }

                if ( $( this ).hasClass( 'small' ) ) {
                    $wrap.addClass( 'small' );
                }

                if ( $( this ).hasClass( 'medium' ) ) {
                    $wrap.addClass( 'medium' );
                }

                id = this.getAttribute( 'id' ) || 'buddyboss-select-' + current;
                $span = $select.prev( 'span' );
                $label = $select.prev( 'label' );

                $inner_wrap = $( '<div class="buddyboss-select-inner"></div>' );

                if ( !$span.length ) {
                    $span = $( '<span></span>' ).hide();
                    dynamic = true;
                }

                if ( !$( this ).parent().hasClass( 'buddyboss-select-inner' ) ) {
                    $select.wrap( $wrap );
                    $label.insertBefore( $select );
                    $select.wrap( $inner_wrap );
                    $span.insertBefore( $select );
                }

                // Set data on select element to use later
                $select.data( 'buddyboss-select-info', {
                    dynamic: dynamic,
                    $wrap: $wrap,
                    $label: $span,
                    orig_text: $span.text()
                } );

                state = 'init';
            }

            $select.data( 'state', state );

            // On select change, repopulate label
            $select.on( 'change', function ( e ) {
                that.populate_select_label( is_mobile );
            } );
        } );
    }
};
// Generated by CoffeeScript 1.6.3
/*
jQuery Growl
Copyright 2013 Kevin Sylvestre
1.2.4
*/


(function() {
  "use strict";
  var $, Animation, Growl,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

  $ = jQuery;

  Animation = (function() {
    function Animation() {}

    Animation.transitions = {
      "webkitTransition": "webkitTransitionEnd",
      "mozTransition": "mozTransitionEnd",
      "oTransition": "oTransitionEnd",
      "transition": "transitionend"
    };

    Animation.transition = function($el) {
      var el, result, type, _ref;
      el = $el[0];
      _ref = this.transitions;
      for (type in _ref) {
        result = _ref[type];
        if (el.style[type] != null) {
          return result;
        }
      }
    };

    return Animation;

  })();

  Growl = (function() {
    Growl.settings = {
      namespace: 'growl',
      duration: 3200,
      close: "&#215;",
      location: "default",
      style: "default",
      size: "medium"
    };

    Growl.growl = function(settings) {
      if (settings == null) {
        settings = {};
      }
      this.initialize();
      return new Growl(settings);
    };

    Growl.initialize = function() {
      return $(".bb-cover-photo:not(:has(#growls))").append('<div id="growls" />');
    };

    function Growl(settings) {
      if (settings == null) {
        settings = {};
      }
      this.html = __bind(this.html, this);
      this.$growl = __bind(this.$growl, this);
      this.$growls = __bind(this.$growls, this);
      this.animate = __bind(this.animate, this);
      this.remove = __bind(this.remove, this);
      this.dismiss = __bind(this.dismiss, this);
      this.present = __bind(this.present, this);
      this.cycle = __bind(this.cycle, this);
      this.close = __bind(this.close, this);
      this.unbind = __bind(this.unbind, this);
      this.bind = __bind(this.bind, this);
      this.render = __bind(this.render, this);
      this.settings = $.extend({}, Growl.settings, settings);
      this.$growls().attr('class', this.settings.location);
      this.render();
    }

    Growl.prototype.render = function() {
      var $growl;
      $growl = this.$growl();
      this.$growls().append($growl);
      if (this.settings["static"] != null) {
        this.present();
      } else {
        this.cycle();
      }
    };

    Growl.prototype.bind = function($growl) {
      if ($growl == null) {
        $growl = this.$growl();
      }
      return $growl.on("contextmenu", this.close).find("." + this.settings.namespace + "-close").on("click", this.close);
    };

    Growl.prototype.unbind = function($growl) {
      if ($growl == null) {
        $growl = this.$growl();
      }
      return $growl.off("contextmenu", this.close).find("." + this.settings.namespace + "-close").off("click", this.close);
    };

    Growl.prototype.close = function(event) {
      var $growl;
      event.preventDefault();
      event.stopPropagation();
      $growl = this.$growl();
      return $growl.stop().queue(this.dismiss).queue(this.remove);
    };

    Growl.prototype.cycle = function() {
      var $growl;
      $growl = this.$growl();
      return $growl.queue(this.present).delay(this.settings.duration).queue(this.dismiss).queue(this.remove);
    };

    Growl.prototype.present = function(callback) {
      var $growl;
      $growl = this.$growl();
      this.bind($growl);
      return this.animate($growl, "" + this.settings.namespace + "-incoming", 'out', callback);
    };

    Growl.prototype.dismiss = function(callback) {
      var $growl;
      $growl = this.$growl();
      this.unbind($growl);
      return this.animate($growl, "" + this.settings.namespace + "-outgoing", 'in', callback);
    };

    Growl.prototype.remove = function(callback) {
      this.$growl().remove();
      return callback();
    };

    Growl.prototype.animate = function($element, name, direction, callback) {
      var transition;
      if (direction == null) {
        direction = 'in';
      }
      transition = Animation.transition($element);
      $element[direction === 'in' ? 'removeClass' : 'addClass'](name);
      $element.offset().position;
      $element[direction === 'in' ? 'addClass' : 'removeClass'](name);
      if (callback == null) {
        return;
      }
      if (transition != null) {
        $element.one(transition, callback);
      } else {
        callback();
      }
    };

    Growl.prototype.$growls = function() {
      return this.$_growls != null ? this.$_growls : this.$_growls = $('#growls');
    };

    Growl.prototype.$growl = function() {
      return this.$_growl != null ? this.$_growl : this.$_growl = $(this.html());
    };

    Growl.prototype.html = function() {
      return "<div class='" + this.settings.namespace + " " + this.settings.namespace + "-" + this.settings.style + " " + this.settings.namespace + "-" + this.settings.size + "'>\n  <div class='" + this.settings.namespace + "-close'>" + this.settings.close + "</div>\n  <div class='" + this.settings.namespace + "-title'>" + this.settings.title + "</div>\n  <div class='" + this.settings.namespace + "-message'>" + this.settings.message + "</div>\n</div>";
    };

    return Growl;

  })();

  $.growl = function(options) {
    if (options == null) {
      options = {};
    }
    return Growl.growl(options);
  };

  $.growl.error = function(options) {
    var settings;
    if (options == null) {
      options = {};
    }
    settings = {
      title: "Error!",
      style: "error"
    };
    return $.growl($.extend(settings, options));
  };

  $.growl.notice = function(options) {
    var settings;
    if (options == null) {
      options = {};
    }
    settings = {
      title: "Notice!",
      style: "notice"
    };
    return $.growl($.extend(settings, options));
  };

  $.growl.warning = function(options) {
    var settings;
    if (options == null) {
      options = {};
    }
    settings = {
      title: "Warning!",
      style: "warning"
    };
    return $.growl($.extend(settings, options));
  };

}).call(this);
/*!
Waypoints - 4.0.0
Copyright © 2011-2015 Caleb Troughton
Licensed under the MIT license.
https://github.com/imakewebthings/waypoints/blog/master/licenses.txt
*/
(function() {
  'use strict'

  var keyCounter = 0
  var allWaypoints = {}

  /* http://imakewebthings.com/waypoints/api/waypoint */
  function Waypoint(options) {
    if (!options) {
      throw new Error('No options passed to Waypoint constructor')
    }
    if (!options.element) {
      throw new Error('No element option passed to Waypoint constructor')
    }
    if (!options.handler) {
      throw new Error('No handler option passed to Waypoint constructor')
    }

    this.key = 'waypoint-' + keyCounter
    this.options = Waypoint.Adapter.extend({}, Waypoint.defaults, options)
    this.element = this.options.element
    this.adapter = new Waypoint.Adapter(this.element)
    this.callback = options.handler
    this.axis = this.options.horizontal ? 'horizontal' : 'vertical'
    this.enabled = this.options.enabled
    this.triggerPoint = null
    this.group = Waypoint.Group.findOrCreate({
      name: this.options.group,
      axis: this.axis
    })
    this.context = Waypoint.Context.findOrCreateByElement(this.options.context)

    if (Waypoint.offsetAliases[this.options.offset]) {
      this.options.offset = Waypoint.offsetAliases[this.options.offset]
    }
    this.group.add(this)
    this.context.add(this)
    allWaypoints[this.key] = this
    keyCounter += 1
  }

  /* Private */
  Waypoint.prototype.queueTrigger = function(direction) {
    this.group.queueTrigger(this, direction)
  }

  /* Private */
  Waypoint.prototype.trigger = function(args) {
    if (!this.enabled) {
      return
    }
    if (this.callback) {
      this.callback.apply(this, args)
    }
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/destroy */
  Waypoint.prototype.destroy = function() {
    this.context.remove(this)
    this.group.remove(this)
    delete allWaypoints[this.key]
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/disable */
  Waypoint.prototype.disable = function() {
    this.enabled = false
    return this
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/enable */
  Waypoint.prototype.enable = function() {
    this.context.refresh()
    this.enabled = true
    return this
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/next */
  Waypoint.prototype.next = function() {
    return this.group.next(this)
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/previous */
  Waypoint.prototype.previous = function() {
    return this.group.previous(this)
  }

  /* Private */
  Waypoint.invokeAll = function(method) {
    var allWaypointsArray = []
    for (var waypointKey in allWaypoints) {
      allWaypointsArray.push(allWaypoints[waypointKey])
    }
    for (var i = 0, end = allWaypointsArray.length; i < end; i++) {
      allWaypointsArray[i][method]()
    }
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/destroy-all */
  Waypoint.destroyAll = function() {
    Waypoint.invokeAll('destroy')
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/disable-all */
  Waypoint.disableAll = function() {
    Waypoint.invokeAll('disable')
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/enable-all */
  Waypoint.enableAll = function() {
    Waypoint.invokeAll('enable')
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/refresh-all */
  Waypoint.refreshAll = function() {
    Waypoint.Context.refreshAll()
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/viewport-height */
  Waypoint.viewportHeight = function() {
    return window.innerHeight || document.documentElement.clientHeight
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/viewport-width */
  Waypoint.viewportWidth = function() {
    return document.documentElement.clientWidth
  }

  Waypoint.adapters = []

  Waypoint.defaults = {
    context: window,
    continuous: true,
    enabled: true,
    group: 'default',
    horizontal: false,
    offset: 0
  }

  Waypoint.offsetAliases = {
    'bottom-in-view': function() {
      return this.context.innerHeight() - this.adapter.outerHeight()
    },
    'right-in-view': function() {
      return this.context.innerWidth() - this.adapter.outerWidth()
    }
  }

  window.Waypoint = Waypoint
}())
;(function() {
  'use strict'

  function requestAnimationFrameShim(callback) {
    window.setTimeout(callback, 1000 / 60)
  }

  var keyCounter = 0
  var contexts = {}
  var Waypoint = window.Waypoint
  var oldWindowLoad = window.onload

  /* http://imakewebthings.com/waypoints/api/context */
  function Context(element) {
    this.element = element
    this.Adapter = Waypoint.Adapter
    this.adapter = new this.Adapter(element)
    this.key = 'waypoint-context-' + keyCounter
    this.didScroll = false
    this.didResize = false
    this.oldScroll = {
      x: this.adapter.scrollLeft(),
      y: this.adapter.scrollTop()
    }
    this.waypoints = {
      vertical: {},
      horizontal: {}
    }

    element.waypointContextKey = this.key
    contexts[element.waypointContextKey] = this
    keyCounter += 1

    this.createThrottledScrollHandler()
    this.createThrottledResizeHandler()
  }

  /* Private */
  Context.prototype.add = function(waypoint) {
    var axis = waypoint.options.horizontal ? 'horizontal' : 'vertical'
    this.waypoints[axis][waypoint.key] = waypoint
    this.refresh()
  }

  /* Private */
  Context.prototype.checkEmpty = function() {
    var horizontalEmpty = this.Adapter.isEmptyObject(this.waypoints.horizontal)
    var verticalEmpty = this.Adapter.isEmptyObject(this.waypoints.vertical)
    if (horizontalEmpty && verticalEmpty) {
      this.adapter.off('.waypoints')
      delete contexts[this.key]
    }
  }

  /* Private */
  Context.prototype.createThrottledResizeHandler = function() {
    var self = this

    function resizeHandler() {
      self.handleResize()
      self.didResize = false
    }

    this.adapter.on('resize.waypoints', function() {
      if (!self.didResize) {
        self.didResize = true
        Waypoint.requestAnimationFrame(resizeHandler)
      }
    })
  }

  /* Private */
  Context.prototype.createThrottledScrollHandler = function() {
    var self = this
    function scrollHandler() {
      self.handleScroll()
      self.didScroll = false
    }

    this.adapter.on('scroll.waypoints', function() {
      if (!self.didScroll || Waypoint.isTouch) {
        self.didScroll = true
        Waypoint.requestAnimationFrame(scrollHandler)
      }
    })
  }

  /* Private */
  Context.prototype.handleResize = function() {
    Waypoint.Context.refreshAll()
  }

  /* Private */
  Context.prototype.handleScroll = function() {
    var triggeredGroups = {}
    var axes = {
      horizontal: {
        newScroll: this.adapter.scrollLeft(),
        oldScroll: this.oldScroll.x,
        forward: 'right',
        backward: 'left'
      },
      vertical: {
        newScroll: this.adapter.scrollTop(),
        oldScroll: this.oldScroll.y,
        forward: 'down',
        backward: 'up'
      }
    }

    for (var axisKey in axes) {
      var axis = axes[axisKey]
      var isForward = axis.newScroll > axis.oldScroll
      var direction = isForward ? axis.forward : axis.backward

      for (var waypointKey in this.waypoints[axisKey]) {
        var waypoint = this.waypoints[axisKey][waypointKey]
        var wasBeforeTriggerPoint = axis.oldScroll < waypoint.triggerPoint
        var nowAfterTriggerPoint = axis.newScroll >= waypoint.triggerPoint
        var crossedForward = wasBeforeTriggerPoint && nowAfterTriggerPoint
        var crossedBackward = !wasBeforeTriggerPoint && !nowAfterTriggerPoint
        if (crossedForward || crossedBackward) {
          waypoint.queueTrigger(direction)
          triggeredGroups[waypoint.group.id] = waypoint.group
        }
      }
    }

    for (var groupKey in triggeredGroups) {
      triggeredGroups[groupKey].flushTriggers()
    }

    this.oldScroll = {
      x: axes.horizontal.newScroll,
      y: axes.vertical.newScroll
    }
  }

  /* Private */
  Context.prototype.innerHeight = function() {
    /*eslint-disable eqeqeq */
    if (this.element == this.element.window) {
      return Waypoint.viewportHeight()
    }
    /*eslint-enable eqeqeq */
    return this.adapter.innerHeight()
  }

  /* Private */
  Context.prototype.remove = function(waypoint) {
    delete this.waypoints[waypoint.axis][waypoint.key]
    this.checkEmpty()
  }

  /* Private */
  Context.prototype.innerWidth = function() {
    /*eslint-disable eqeqeq */
    if (this.element == this.element.window) {
      return Waypoint.viewportWidth()
    }
    /*eslint-enable eqeqeq */
    return this.adapter.innerWidth()
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/context-destroy */
  Context.prototype.destroy = function() {
    var allWaypoints = []
    for (var axis in this.waypoints) {
      for (var waypointKey in this.waypoints[axis]) {
        allWaypoints.push(this.waypoints[axis][waypointKey])
      }
    }
    for (var i = 0, end = allWaypoints.length; i < end; i++) {
      allWaypoints[i].destroy()
    }
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/context-refresh */
  Context.prototype.refresh = function() {
    /*eslint-disable eqeqeq */
    var isWindow = this.element == this.element.window
    /*eslint-enable eqeqeq */
    var contextOffset = isWindow ? undefined : this.adapter.offset()
    var triggeredGroups = {}
    var axes

    this.handleScroll()
    axes = {
      horizontal: {
        contextOffset: isWindow ? 0 : contextOffset.left,
        contextScroll: isWindow ? 0 : this.oldScroll.x,
        contextDimension: this.innerWidth(),
        oldScroll: this.oldScroll.x,
        forward: 'right',
        backward: 'left',
        offsetProp: 'left'
      },
      vertical: {
        contextOffset: isWindow ? 0 : contextOffset.top,
        contextScroll: isWindow ? 0 : this.oldScroll.y,
        contextDimension: this.innerHeight(),
        oldScroll: this.oldScroll.y,
        forward: 'down',
        backward: 'up',
        offsetProp: 'top'
      }
    }

    for (var axisKey in axes) {
      var axis = axes[axisKey]
      for (var waypointKey in this.waypoints[axisKey]) {
        var waypoint = this.waypoints[axisKey][waypointKey]
        var adjustment = waypoint.options.offset
        var oldTriggerPoint = waypoint.triggerPoint
        var elementOffset = 0
        var freshWaypoint = oldTriggerPoint == null
        var contextModifier, wasBeforeScroll, nowAfterScroll
        var triggeredBackward, triggeredForward

        if (waypoint.element !== waypoint.element.window) {
          elementOffset = waypoint.adapter.offset()[axis.offsetProp]
        }

        if (typeof adjustment === 'function') {
          adjustment = adjustment.apply(waypoint)
        }
        else if (typeof adjustment === 'string') {
          adjustment = parseFloat(adjustment)
          if (waypoint.options.offset.indexOf('%') > - 1) {
            adjustment = Math.ceil(axis.contextDimension * adjustment / 100)
          }
        }

        contextModifier = axis.contextScroll - axis.contextOffset
        waypoint.triggerPoint = elementOffset + contextModifier - adjustment
        wasBeforeScroll = oldTriggerPoint < axis.oldScroll
        nowAfterScroll = waypoint.triggerPoint >= axis.oldScroll
        triggeredBackward = wasBeforeScroll && nowAfterScroll
        triggeredForward = !wasBeforeScroll && !nowAfterScroll

        if (!freshWaypoint && triggeredBackward) {
          waypoint.queueTrigger(axis.backward)
          triggeredGroups[waypoint.group.id] = waypoint.group
        }
        else if (!freshWaypoint && triggeredForward) {
          waypoint.queueTrigger(axis.forward)
          triggeredGroups[waypoint.group.id] = waypoint.group
        }
        else if (freshWaypoint && axis.oldScroll >= waypoint.triggerPoint) {
          waypoint.queueTrigger(axis.forward)
          triggeredGroups[waypoint.group.id] = waypoint.group
        }
      }
    }

    Waypoint.requestAnimationFrame(function() {
      for (var groupKey in triggeredGroups) {
        triggeredGroups[groupKey].flushTriggers()
      }
    })

    return this
  }

  /* Private */
  Context.findOrCreateByElement = function(element) {
    return Context.findByElement(element) || new Context(element)
  }

  /* Private */
  Context.refreshAll = function() {
    for (var contextId in contexts) {
      contexts[contextId].refresh()
    }
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/context-find-by-element */
  Context.findByElement = function(element) {
    return contexts[element.waypointContextKey]
  }

  window.onload = function() {
    if (oldWindowLoad) {
      oldWindowLoad()
    }
    Context.refreshAll()
  }

  Waypoint.requestAnimationFrame = function(callback) {
    var requestFn = window.requestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      requestAnimationFrameShim
    requestFn.call(window, callback)
  }
  Waypoint.Context = Context
}())
;(function() {
  'use strict'

  function byTriggerPoint(a, b) {
    return a.triggerPoint - b.triggerPoint
  }

  function byReverseTriggerPoint(a, b) {
    return b.triggerPoint - a.triggerPoint
  }

  var groups = {
    vertical: {},
    horizontal: {}
  }
  var Waypoint = window.Waypoint

  /* http://imakewebthings.com/waypoints/api/group */
  function Group(options) {
    this.name = options.name
    this.axis = options.axis
    this.id = this.name + '-' + this.axis
    this.waypoints = []
    this.clearTriggerQueues()
    groups[this.axis][this.name] = this
  }

  /* Private */
  Group.prototype.add = function(waypoint) {
    this.waypoints.push(waypoint)
  }

  /* Private */
  Group.prototype.clearTriggerQueues = function() {
    this.triggerQueues = {
      up: [],
      down: [],
      left: [],
      right: []
    }
  }

  /* Private */
  Group.prototype.flushTriggers = function() {
    for (var direction in this.triggerQueues) {
      var waypoints = this.triggerQueues[direction]
      var reverse = direction === 'up' || direction === 'left'
      waypoints.sort(reverse ? byReverseTriggerPoint : byTriggerPoint)
      for (var i = 0, end = waypoints.length; i < end; i += 1) {
        var waypoint = waypoints[i]
        if (waypoint.options.continuous || i === waypoints.length - 1) {
          waypoint.trigger([direction])
        }
      }
    }
    this.clearTriggerQueues()
  }

  /* Private */
  Group.prototype.next = function(waypoint) {
    this.waypoints.sort(byTriggerPoint)
    var index = Waypoint.Adapter.inArray(waypoint, this.waypoints)
    var isLast = index === this.waypoints.length - 1
    return isLast ? null : this.waypoints[index + 1]
  }

  /* Private */
  Group.prototype.previous = function(waypoint) {
    this.waypoints.sort(byTriggerPoint)
    var index = Waypoint.Adapter.inArray(waypoint, this.waypoints)
    return index ? this.waypoints[index - 1] : null
  }

  /* Private */
  Group.prototype.queueTrigger = function(waypoint, direction) {
    this.triggerQueues[direction].push(waypoint)
  }

  /* Private */
  Group.prototype.remove = function(waypoint) {
    var index = Waypoint.Adapter.inArray(waypoint, this.waypoints)
    if (index > -1) {
      this.waypoints.splice(index, 1)
    }
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/first */
  Group.prototype.first = function() {
    return this.waypoints[0]
  }

  /* Public */
  /* http://imakewebthings.com/waypoints/api/last */
  Group.prototype.last = function() {
    return this.waypoints[this.waypoints.length - 1]
  }

  /* Private */
  Group.findOrCreate = function(options) {
    return groups[options.axis][options.name] || new Group(options)
  }

  Waypoint.Group = Group
}())
;(function() {
  'use strict'

  var $ = window.jQuery
  var Waypoint = window.Waypoint

  function JQueryAdapter(element) {
    this.$element = $(element)
  }

  $.each([
    'innerHeight',
    'innerWidth',
    'off',
    'offset',
    'on',
    'outerHeight',
    'outerWidth',
    'scrollLeft',
    'scrollTop'
  ], function(i, method) {
    JQueryAdapter.prototype[method] = function() {
      var args = Array.prototype.slice.call(arguments)
      return this.$element[method].apply(this.$element, args)
    }
  })

  $.each([
    'extend',
    'inArray',
    'isEmptyObject'
  ], function(i, method) {
    JQueryAdapter[method] = $[method]
  })

  Waypoint.adapters.push({
    name: 'jquery',
    Adapter: JQueryAdapter
  })
  Waypoint.Adapter = JQueryAdapter
}())
;(function() {
  'use strict'

  var Waypoint = window.Waypoint

  function createExtension(framework) {
    return function() {
      var waypoints = []
      var overrides = arguments[0]

      if (framework.isFunction(arguments[0])) {
        overrides = framework.extend({}, arguments[1])
        overrides.handler = arguments[0]
      }

      this.each(function() {
        var options = framework.extend({}, overrides, {
          element: this
        })
        if (typeof options.context === 'string') {
          options.context = framework(this).closest(options.context)[0]
        }
        waypoints.push(new Waypoint(options))
      })

      return waypoints
    }
  }

  if (window.jQuery) {
    window.jQuery.fn.waypoint = createExtension(window.jQuery)
  }
  if (window.Zepto) {
    window.Zepto.fn.waypoint = createExtension(window.Zepto)
  }
}())
;
/*
 * CSS3 Animate it
 * Copyright (c) 2014 Jack McCourt
 * https://github.com/kriegar/css3-animate-it
 * Version: 0.1.0
 *
 * I utilise the jQuery.appear plugin within this javascript file so here is a link to that too
 * https://github.com/morr/jquery.appear
 *
 * I also utilise the jQuery.doTimeout plugin for the data-sequence functionality so here is a link back to them.
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 */
( function ( $ ) {
    var selectors = [ ];

    var check_binded = false;
    var check_lock = false;
    var defaults = {
        interval: 250,
        force_process: false
    }
    var $window = $( window );

    var $prior_appeared;

    function process() {
        check_lock = false;
        for ( var index = 0; index < selectors.length; index++ ) {
            var $appeared = $( selectors[index] ).filter( function () {
                return $( this ).is( ':appeared' );
            } );

            $appeared.trigger( 'appear', [ $appeared ] );

            if ( $prior_appeared ) {

                var $disappeared = $prior_appeared.not( $appeared );
                $disappeared.trigger( 'disappear', [ $disappeared ] );
            }
            $prior_appeared = $appeared;
        }
    }

    // "appeared" custom filter
    $.expr[':']['appeared'] = function ( element ) {
        var $element = $( element );
        if ( !$element.is( ':visible' ) ) {
            return false;
        }

        var window_left = $window.scrollLeft();
        var window_top = $window.scrollTop();
        var offset = $element.offset();
        var left = offset.left;
        var top = offset.top;

        if ( top + $element.height() >= window_top &&
            top - ( $element.data( 'appear-top-offset' ) || 0 ) <= window_top + $window.height() &&
            left + $element.width() >= window_left &&
            left - ( $element.data( 'appear-left-offset' ) || 0 ) <= window_left + $window.width() ) {
            return true;
        } else {
            return false;
        }
    }

    $.fn.extend( {
        // watching for element's appearance in browser viewport
        appear: function ( options ) {
            var opts = $.extend( { }, defaults, options || { } );
            var selector = this.selector || this;
            if ( !check_binded ) {
                var on_check = function () {
                    if ( check_lock ) {
                        return;
                    }
                    check_lock = true;

                    setTimeout( process, opts.interval );
                };

                $( window ).scroll( on_check ).resize( on_check );
                check_binded = true;
            }

            if ( opts.force_process ) {
                setTimeout( process, opts.interval );
            }
            selectors.push( selector );
            return $( selector );
        }
    } );

    $.extend( {
        // force elements's appearance check
        force_appear: function () {
            if ( check_binded ) {
                process();
                return true;
            }
            ;
            return false;
        }
    } );
} )( jQuery );



/*!
 * jQuery doTimeout: Like setTimeout, but better! - v1.0 - 3/3/2010
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

// Script: jQuery doTimeout: Like setTimeout, but better!
//
// *Version: 1.0, Last updated: 3/3/2010*
//
// Project Home - http://benalman.com/projects/jquery-dotimeout-plugin/
// GitHub       - http://github.com/cowboy/jquery-dotimeout/
// Source       - http://github.com/cowboy/jquery-dotimeout/raw/master/jquery.ba-dotimeout.js
// (Minified)   - http://github.com/cowboy/jquery-dotimeout/raw/master/jquery.ba-dotimeout.min.js (1.0kb)
//
// About: License
//
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
//
// About: Examples
//
// These working examples, complete with fully commented code, illustrate a few
// ways in which this plugin can be used.
//
// Debouncing      - http://benalman.com/code/projects/jquery-dotimeout/examples/debouncing/
// Delays, Polling - http://benalman.com/code/projects/jquery-dotimeout/examples/delay-poll/
// Hover Intent    - http://benalman.com/code/projects/jquery-dotimeout/examples/hoverintent/
//
// About: Support and Testing
//
// Information about what version or versions of jQuery this plugin has been
// tested with, what browsers it has been tested in, and where the unit tests
// reside (so you can test it yourself).
//
// jQuery Versions - 1.3.2, 1.4.2
// Browsers Tested - Internet Explorer 6-8, Firefox 2-3.6, Safari 3-4, Chrome 4-5, Opera 9.6-10.1.
// Unit Tests      - http://benalman.com/code/projects/jquery-dotimeout/unit/
//
// About: Release History
//
// 1.0 - (3/3/2010) Callback can now be a string, in which case it will call
//       the appropriate $.method or $.fn.method, depending on where .doTimeout
//       was called. Callback must now return `true` (not just a truthy value)
//       to poll.
// 0.4 - (7/15/2009) Made the "id" argument optional, some other minor tweaks
// 0.3 - (6/25/2009) Initial release

( function ( $ ) {
    '$:nomunge'; // Used by YUI compressor.

    var cache = { },
        // Reused internal string.
        doTimeout = 'doTimeout',
        // A convenient shortcut.
        aps = Array.prototype.slice;

    // Method: jQuery.doTimeout
    //
    // Initialize, cancel, or force execution of a callback after a delay.
    //
    // If delay and callback are specified, a doTimeout is initialized. The
    // callback will execute, asynchronously, after the delay. If an id is
    // specified, this doTimeout will override and cancel any existing doTimeout
    // with the same id. Any additional arguments will be passed into callback
    // when it is executed.
    //
    // If the callback returns true, the doTimeout loop will execute again, after
    // the delay, creating a polling loop until the callback returns a non-true
    // value.
    //
    // Note that if an id is not passed as the first argument, this doTimeout will
    // NOT be able to be manually canceled or forced. (for debouncing, be sure to
    // specify an id).
    //
    // If id is specified, but delay and callback are not, the doTimeout will be
    // canceled without executing the callback. If force_mode is specified, the
    // callback will be executed, synchronously, but will only be allowed to
    // continue a polling loop if force_mode is true (provided the callback
    // returns true, of course). If force_mode is false, no polling loop will
    // continue, even if the callback returns true.
    //
    // Usage:
    //
    // > jQuery.doTimeout( [ id, ] delay, callback [, arg ... ] );
    // > jQuery.doTimeout( id [, force_mode ] );
    //
    // Arguments:
    //
    //  id - (String) An optional unique identifier for this doTimeout. If id is
    //    not specified, the doTimeout will NOT be able to be manually canceled or
    //    forced.
    //  delay - (Number) A zero-or-greater delay in milliseconds after which
    //    callback will be executed.
    //  callback - (Function) A function to be executed after delay milliseconds.
    //  callback - (String) A jQuery method to be executed after delay
    //    milliseconds. This method will only poll if it explicitly returns
    //    true.
    //  force_mode - (Boolean) If true, execute that id's doTimeout callback
    //    immediately and synchronously, continuing any callback return-true
    //    polling loop. If false, execute the callback immediately and
    //    synchronously but do NOT continue a callback return-true polling loop.
    //    If omitted, cancel that id's doTimeout.
    //
    // Returns:
    //
    //  If force_mode is true, false or undefined and there is a
    //  yet-to-be-executed callback to cancel, true is returned, but if no
    //  callback remains to be executed, undefined is returned.

    $[doTimeout] = function () {
        return p_doTimeout.apply( window, [ 0 ].concat( aps.call( arguments ) ) );
    };

    // Method: jQuery.fn.doTimeout
    //
    // Initialize, cancel, or force execution of a callback after a delay.
    // Operates like <jQuery.doTimeout>, but the passed callback executes in the
    // context of the jQuery collection of elements, and the id is stored as data
    // on the first element in that collection.
    //
    // If delay and callback are specified, a doTimeout is initialized. The
    // callback will execute, asynchronously, after the delay. If an id is
    // specified, this doTimeout will override and cancel any existing doTimeout
    // with the same id. Any additional arguments will be passed into callback
    // when it is executed.
    //
    // If the callback returns true, the doTimeout loop will execute again, after
    // the delay, creating a polling loop until the callback returns a non-true
    // value.
    //
    // Note that if an id is not passed as the first argument, this doTimeout will
    // NOT be able to be manually canceled or forced (for debouncing, be sure to
    // specify an id).
    //
    // If id is specified, but delay and callback are not, the doTimeout will be
    // canceled without executing the callback. If force_mode is specified, the
    // callback will be executed, synchronously, but will only be allowed to
    // continue a polling loop if force_mode is true (provided the callback
    // returns true, of course). If force_mode is false, no polling loop will
    // continue, even if the callback returns true.
    //
    // Usage:
    //
    // > jQuery('selector').doTimeout( [ id, ] delay, callback [, arg ... ] );
    // > jQuery('selector').doTimeout( id [, force_mode ] );
    //
    // Arguments:
    //
    //  id - (String) An optional unique identifier for this doTimeout, stored as
    //    jQuery data on the element. If id is not specified, the doTimeout will
    //    NOT be able to be manually canceled or forced.
    //  delay - (Number) A zero-or-greater delay in milliseconds after which
    //    callback will be executed.
    //  callback - (Function) A function to be executed after delay milliseconds.
    //  callback - (String) A jQuery.fn method to be executed after delay
    //    milliseconds. This method will only poll if it explicitly returns
    //    true (most jQuery.fn methods return a jQuery object, and not `true`,
    //    which allows them to be chained and prevents polling).
    //  force_mode - (Boolean) If true, execute that id's doTimeout callback
    //    immediately and synchronously, continuing any callback return-true
    //    polling loop. If false, execute the callback immediately and
    //    synchronously but do NOT continue a callback return-true polling loop.
    //    If omitted, cancel that id's doTimeout.
    //
    // Returns:
    //
    //  When creating a <jQuery.fn.doTimeout>, the initial jQuery collection of
    //  elements is returned. Otherwise, if force_mode is true, false or undefined
    //  and there is a yet-to-be-executed callback to cancel, true is returned,
    //  but if no callback remains to be executed, undefined is returned.

    $.fn[doTimeout] = function () {
        var args = aps.call( arguments ),
            result = p_doTimeout.apply( this, [ doTimeout + args[0] ].concat( args ) );

        return typeof args[0] === 'number' || typeof args[1] === 'number'
            ? this
            : result;
    };

    function p_doTimeout( jquery_data_key ) {
        var that = this,
            elem,
            data = { },
            // Allows the plugin to call a string callback method.
            method_base = jquery_data_key ? $.fn : $,
            // Any additional arguments will be passed to the callback.
            args = arguments,
            slice_args = 4,
            id = args[1],
            delay = args[2],
            callback = args[3];

        if ( typeof id !== 'string' ) {
            slice_args--;

            id = jquery_data_key = 0;
            delay = args[1];
            callback = args[2];
        }

        // If id is passed, store a data reference either as .data on the first
        // element in a jQuery collection, or in the internal cache.
        if ( jquery_data_key ) { // Note: key is 'doTimeout' + id

            // Get id-object from the first element's data, otherwise initialize it to {}.
            elem = that.eq( 0 );
            elem.data( jquery_data_key, data = elem.data( jquery_data_key ) || { } );

        } else if ( id ) {
            // Get id-object from the cache, otherwise initialize it to {}.
            data = cache[ id ] || ( cache[ id ] = { } );
        }

        // Clear any existing timeout for this id.
        data.id && clearTimeout( data.id );
        delete data.id;

        // Clean up when necessary.
        function cleanup() {
            if ( jquery_data_key ) {
                elem.removeData( jquery_data_key );
            } else if ( id ) {
                delete cache[ id ];
            }
        }
        ;

        // Yes, there actually is a setTimeout call in here!
        function actually_setTimeout() {
            data.id = setTimeout( function () {
                data.fn();
            }, delay );
        }
        ;

        if ( callback ) {
            // A callback (and delay) were specified. Store the callback reference for
            // possible later use, and then setTimeout.
            data.fn = function ( no_polling_loop ) {

                // If the callback value is a string, it is assumed to be the name of a
                // method on $ or $.fn depending on where doTimeout was executed.
                if ( typeof callback === 'string' ) {
                    callback = method_base[ callback ];
                }

                callback.apply( that, aps.call( args, slice_args ) ) === true && !no_polling_loop

                    // Since the callback returned true, and we're not specifically
                    // canceling a polling loop, do it again!
                    ? actually_setTimeout()

                    // Otherwise, clean up and quit.
                    : cleanup();
            };

            // Set that timeout!
            actually_setTimeout();

        } else if ( data.fn ) {
            // No callback passed. If force_mode (delay) is true, execute the data.fn
            // callback immediately, continuing any callback return-true polling loop.
            // If force_mode is false, execute the data.fn callback immediately but do
            // NOT continue a callback return-true polling loop. If force_mode is
            // undefined, simply clean up. Since data.fn was still defined, whatever
            // was supposed to happen hadn't yet, so return true.
            delay === undefined ? cleanup() : data.fn( delay === false );
            return true;

        } else {
            // Since no callback was passed, and data.fn isn't defined, it looks like
            // whatever was supposed to happen already did. Clean up and quit!
            cleanup();
        }

    }
    ;

} )( jQuery );


jQuery( document ).ready( function ( $ ) {

//CSS3 Animate-it
    $( '.animatedParent' ).appear();
    $( '.animatedClick' ).click( function () {
        var target = $( this ).attr( 'data-target' );


        if ( $( this ).attr( 'data-sequence' ) !== undefined ) {
            var firstId = $( "." + target + ":first" ).attr( 'data-id' );
            var lastId = $( "." + target + ":last" ).attr( 'data-id' );
            var number = firstId;

            //Add or remove the class
            if ( $( "." + target + "[data-id=" + number + "]" ).hasClass( 'go' ) ) {
                $( "." + target + "[data-id=" + number + "]" ).addClass( 'goAway' );
                $( "." + target + "[data-id=" + number + "]" ).removeClass( 'go' );
            } else {
                $( "." + target + "[data-id=" + number + "]" ).addClass( 'go' );
                $( "." + target + "[data-id=" + number + "]" ).removeClass( 'goAway' );
            }
            number++;
            delay = Number( $( this ).attr( 'data-sequence' ) );
            $.doTimeout( delay, function () {
                console.log( lastId );

                //Add or remove the class
                if ( $( "." + target + "[data-id=" + number + "]" ).hasClass( 'go' ) ) {
                    $( "." + target + "[data-id=" + number + "]" ).addClass( 'goAway' );
                    $( "." + target + "[data-id=" + number + "]" ).removeClass( 'go' );
                } else {
                    $( "." + target + "[data-id=" + number + "]" ).addClass( 'go' );
                    $( "." + target + "[data-id=" + number + "]" ).removeClass( 'goAway' );
                }

                //increment
                ++number;

                //continute looping till reached last ID
                if ( number <= lastId ) {
                    return true;
                }
            } );
        } else {
            if ( $( '.' + target ).hasClass( 'go' ) ) {
                $( '.' + target ).addClass( 'goAway' );
                $( '.' + target ).removeClass( 'go' );
            } else {
                $( '.' + target ).addClass( 'go' );
                $( '.' + target ).removeClass( 'goAway' );
            }
        }
    } );

    $( document.body ).on( 'appear', '.animatedParent', function ( e, $affected ) {
        var ele = $( this ).find( '.animated' );
        var parent = $( this );


        if ( parent.attr( 'data-sequence' ) != undefined ) {

            var firstId = $( this ).find( '.animated:first' ).attr( 'data-id' );
            var number = firstId;
            var lastId = $( this ).find( '.animated:last' ).attr( 'data-id' );

            $( parent ).find( ".animated[data-id=" + number + "]" ).addClass( 'go' );
            number++;
            delay = Number( parent.attr( 'data-sequence' ) );

            $.doTimeout( delay, function () {
                $( parent ).find( ".animated[data-id=" + number + "]" ).addClass( 'go' );
                ++number;
                if ( number <= lastId ) {
                    return true;
                }
            } );
        } else {
            ele.addClass( 'go' );
        }

    } );

    $( document.body ).on( 'disappear', '.animatedParent', function ( e, $affected ) {
        if ( !$( this ).hasClass( 'animateOnce' ) ) {
            $( this ).find( '.animated' ).removeClass( 'go' );
        }
    } );

    $( window ).load( function () {
        $.force_appear();
    } );

} );
/*! Magnific Popup - v1.0.0 - 2015-01-03
* http://dimsemenov.com/plugins/magnific-popup/
* Copyright (c) 2015 Dmitry Semenov; */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a("object"==typeof exports?require("jquery"):window.jQuery||window.Zepto)}(function(a){var b,c,d,e,f,g,h="Close",i="BeforeClose",j="AfterClose",k="BeforeAppend",l="MarkupParse",m="Open",n="Change",o="mfp",p="."+o,q="mfp-ready",r="mfp-removing",s="mfp-prevent-close",t=function(){},u=!!window.jQuery,v=a(window),w=function(a,c){b.ev.on(o+a+p,c)},x=function(b,c,d,e){var f=document.createElement("div");return f.className="mfp-"+b,d&&(f.innerHTML=d),e?c&&c.appendChild(f):(f=a(f),c&&f.appendTo(c)),f},y=function(c,d){b.ev.triggerHandler(o+c,d),b.st.callbacks&&(c=c.charAt(0).toLowerCase()+c.slice(1),b.st.callbacks[c]&&b.st.callbacks[c].apply(b,a.isArray(d)?d:[d]))},z=function(c){return c===g&&b.currTemplate.closeBtn||(b.currTemplate.closeBtn=a(b.st.closeMarkup.replace("%title%",b.st.tClose)),g=c),b.currTemplate.closeBtn},A=function(){a.magnificPopup.instance||(b=new t,b.init(),a.magnificPopup.instance=b)},B=function(){var a=document.createElement("p").style,b=["ms","O","Moz","Webkit"];if(void 0!==a.transition)return!0;for(;b.length;)if(b.pop()+"Transition"in a)return!0;return!1};t.prototype={constructor:t,init:function(){var c=navigator.appVersion;b.isIE7=-1!==c.indexOf("MSIE 7."),b.isIE8=-1!==c.indexOf("MSIE 8."),b.isLowIE=b.isIE7||b.isIE8,b.isAndroid=/android/gi.test(c),b.isIOS=/iphone|ipad|ipod/gi.test(c),b.supportsTransition=B(),b.probablyMobile=b.isAndroid||b.isIOS||/(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent),d=a(document),b.popupsCache={}},open:function(c){var e;if(c.isObj===!1){b.items=c.items.toArray(),b.index=0;var g,h=c.items;for(e=0;e<h.length;e++)if(g=h[e],g.parsed&&(g=g.el[0]),g===c.el[0]){b.index=e;break}}else b.items=a.isArray(c.items)?c.items:[c.items],b.index=c.index||0;if(b.isOpen)return void b.updateItemHTML();b.types=[],f="",b.ev=c.mainEl&&c.mainEl.length?c.mainEl.eq(0):d,c.key?(b.popupsCache[c.key]||(b.popupsCache[c.key]={}),b.currTemplate=b.popupsCache[c.key]):b.currTemplate={},b.st=a.extend(!0,{},a.magnificPopup.defaults,c),b.fixedContentPos="auto"===b.st.fixedContentPos?!b.probablyMobile:b.st.fixedContentPos,b.st.modal&&(b.st.closeOnContentClick=!1,b.st.closeOnBgClick=!1,b.st.showCloseBtn=!1,b.st.enableEscapeKey=!1),b.bgOverlay||(b.bgOverlay=x("bg").on("click"+p,function(){b.close()}),b.wrap=x("wrap").attr("tabindex",-1).on("click"+p,function(a){b._checkIfClose(a.target)&&b.close()}),b.container=x("container",b.wrap)),b.contentContainer=x("content"),b.st.preloader&&(b.preloader=x("preloader",b.container,b.st.tLoading));var i=a.magnificPopup.modules;for(e=0;e<i.length;e++){var j=i[e];j=j.charAt(0).toUpperCase()+j.slice(1),b["init"+j].call(b)}y("BeforeOpen"),b.st.showCloseBtn&&(b.st.closeBtnInside?(w(l,function(a,b,c,d){c.close_replaceWith=z(d.type)}),f+=" mfp-close-btn-in"):b.wrap.append(z())),b.st.alignTop&&(f+=" mfp-align-top"),b.wrap.css(b.fixedContentPos?{overflow:b.st.overflowY,overflowX:"hidden",overflowY:b.st.overflowY}:{top:v.scrollTop(),position:"absolute"}),(b.st.fixedBgPos===!1||"auto"===b.st.fixedBgPos&&!b.fixedContentPos)&&b.bgOverlay.css({height:d.height(),position:"absolute"}),b.st.enableEscapeKey&&d.on("keyup"+p,function(a){27===a.keyCode&&b.close()}),v.on("resize"+p,function(){b.updateSize()}),b.st.closeOnContentClick||(f+=" mfp-auto-cursor"),f&&b.wrap.addClass(f);var k=b.wH=v.height(),n={};if(b.fixedContentPos&&b._hasScrollBar(k)){var o=b._getScrollbarSize();o&&(n.marginRight=o)}b.fixedContentPos&&(b.isIE7?a("body, html").css("overflow","hidden"):n.overflow="hidden");var r=b.st.mainClass;return b.isIE7&&(r+=" mfp-ie7"),r&&b._addClassToMFP(r),b.updateItemHTML(),y("BuildControls"),a("html").css(n),b.bgOverlay.add(b.wrap).prependTo(b.st.prependTo||a(document.body)),b._lastFocusedEl=document.activeElement,setTimeout(function(){b.content?(b._addClassToMFP(q),b._setFocus()):b.bgOverlay.addClass(q),d.on("focusin"+p,b._onFocusIn)},16),b.isOpen=!0,b.updateSize(k),y(m),c},close:function(){b.isOpen&&(y(i),b.isOpen=!1,b.st.removalDelay&&!b.isLowIE&&b.supportsTransition?(b._addClassToMFP(r),setTimeout(function(){b._close()},b.st.removalDelay)):b._close())},_close:function(){y(h);var c=r+" "+q+" ";if(b.bgOverlay.detach(),b.wrap.detach(),b.container.empty(),b.st.mainClass&&(c+=b.st.mainClass+" "),b._removeClassFromMFP(c),b.fixedContentPos){var e={marginRight:""};b.isIE7?a("body, html").css("overflow",""):e.overflow="",a("html").css(e)}d.off("keyup"+p+" focusin"+p),b.ev.off(p),b.wrap.attr("class","mfp-wrap").removeAttr("style"),b.bgOverlay.attr("class","mfp-bg"),b.container.attr("class","mfp-container"),!b.st.showCloseBtn||b.st.closeBtnInside&&b.currTemplate[b.currItem.type]!==!0||b.currTemplate.closeBtn&&b.currTemplate.closeBtn.detach(),b._lastFocusedEl&&a(b._lastFocusedEl).focus(),b.currItem=null,b.content=null,b.currTemplate=null,b.prevHeight=0,y(j)},updateSize:function(a){if(b.isIOS){var c=document.documentElement.clientWidth/window.innerWidth,d=window.innerHeight*c;b.wrap.css("height",d),b.wH=d}else b.wH=a||v.height();b.fixedContentPos||b.wrap.css("height",b.wH),y("Resize")},updateItemHTML:function(){var c=b.items[b.index];b.contentContainer.detach(),b.content&&b.content.detach(),c.parsed||(c=b.parseEl(b.index));var d=c.type;if(y("BeforeChange",[b.currItem?b.currItem.type:"",d]),b.currItem=c,!b.currTemplate[d]){var f=b.st[d]?b.st[d].markup:!1;y("FirstMarkupParse",f),b.currTemplate[d]=f?a(f):!0}e&&e!==c.type&&b.container.removeClass("mfp-"+e+"-holder");var g=b["get"+d.charAt(0).toUpperCase()+d.slice(1)](c,b.currTemplate[d]);b.appendContent(g,d),c.preloaded=!0,y(n,c),e=c.type,b.container.prepend(b.contentContainer),y("AfterChange")},appendContent:function(a,c){b.content=a,a?b.st.showCloseBtn&&b.st.closeBtnInside&&b.currTemplate[c]===!0?b.content.find(".mfp-close").length||b.content.append(z()):b.content=a:b.content="",y(k),b.container.addClass("mfp-"+c+"-holder"),b.contentContainer.append(b.content)},parseEl:function(c){var d,e=b.items[c];if(e.tagName?e={el:a(e)}:(d=e.type,e={data:e,src:e.src}),e.el){for(var f=b.types,g=0;g<f.length;g++)if(e.el.hasClass("mfp-"+f[g])){d=f[g];break}e.src=e.el.attr("data-mfp-src"),e.src||(e.src=e.el.attr("href"))}return e.type=d||b.st.type||"inline",e.index=c,e.parsed=!0,b.items[c]=e,y("ElementParse",e),b.items[c]},addGroup:function(a,c){var d=function(d){d.mfpEl=this,b._openClick(d,a,c)};c||(c={});var e="click.magnificPopup";c.mainEl=a,c.items?(c.isObj=!0,a.off(e).on(e,d)):(c.isObj=!1,c.delegate?a.off(e).on(e,c.delegate,d):(c.items=a,a.off(e).on(e,d)))},_openClick:function(c,d,e){var f=void 0!==e.midClick?e.midClick:a.magnificPopup.defaults.midClick;if(f||2!==c.which&&!c.ctrlKey&&!c.metaKey){var g=void 0!==e.disableOn?e.disableOn:a.magnificPopup.defaults.disableOn;if(g)if(a.isFunction(g)){if(!g.call(b))return!0}else if(v.width()<g)return!0;c.type&&(c.preventDefault(),b.isOpen&&c.stopPropagation()),e.el=a(c.mfpEl),e.delegate&&(e.items=d.find(e.delegate)),b.open(e)}},updateStatus:function(a,d){if(b.preloader){c!==a&&b.container.removeClass("mfp-s-"+c),d||"loading"!==a||(d=b.st.tLoading);var e={status:a,text:d};y("UpdateStatus",e),a=e.status,d=e.text,b.preloader.html(d),b.preloader.find("a").on("click",function(a){a.stopImmediatePropagation()}),b.container.addClass("mfp-s-"+a),c=a}},_checkIfClose:function(c){if(!a(c).hasClass(s)){var d=b.st.closeOnContentClick,e=b.st.closeOnBgClick;if(d&&e)return!0;if(!b.content||a(c).hasClass("mfp-close")||b.preloader&&c===b.preloader[0])return!0;if(c===b.content[0]||a.contains(b.content[0],c)){if(d)return!0}else if(e&&a.contains(document,c))return!0;return!1}},_addClassToMFP:function(a){b.bgOverlay.addClass(a),b.wrap.addClass(a)},_removeClassFromMFP:function(a){this.bgOverlay.removeClass(a),b.wrap.removeClass(a)},_hasScrollBar:function(a){return(b.isIE7?d.height():document.body.scrollHeight)>(a||v.height())},_setFocus:function(){(b.st.focus?b.content.find(b.st.focus).eq(0):b.wrap).focus()},_onFocusIn:function(c){return c.target===b.wrap[0]||a.contains(b.wrap[0],c.target)?void 0:(b._setFocus(),!1)},_parseMarkup:function(b,c,d){var e;d.data&&(c=a.extend(d.data,c)),y(l,[b,c,d]),a.each(c,function(a,c){if(void 0===c||c===!1)return!0;if(e=a.split("_"),e.length>1){var d=b.find(p+"-"+e[0]);if(d.length>0){var f=e[1];"replaceWith"===f?d[0]!==c[0]&&d.replaceWith(c):"img"===f?d.is("img")?d.attr("src",c):d.replaceWith('<img src="'+c+'" class="'+d.attr("class")+'" />'):d.attr(e[1],c)}}else b.find(p+"-"+a).html(c)})},_getScrollbarSize:function(){if(void 0===b.scrollbarSize){var a=document.createElement("div");a.style.cssText="width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;",document.body.appendChild(a),b.scrollbarSize=a.offsetWidth-a.clientWidth,document.body.removeChild(a)}return b.scrollbarSize}},a.magnificPopup={instance:null,proto:t.prototype,modules:[],open:function(b,c){return A(),b=b?a.extend(!0,{},b):{},b.isObj=!0,b.index=c||0,this.instance.open(b)},close:function(){return a.magnificPopup.instance&&a.magnificPopup.instance.close()},registerModule:function(b,c){c.options&&(a.magnificPopup.defaults[b]=c.options),a.extend(this.proto,c.proto),this.modules.push(b)},defaults:{disableOn:0,key:null,midClick:!1,mainClass:"",preloader:!0,focus:"",closeOnContentClick:!1,closeOnBgClick:!0,closeBtnInside:!0,showCloseBtn:!0,enableEscapeKey:!0,modal:!1,alignTop:!1,removalDelay:0,prependTo:null,fixedContentPos:"auto",fixedBgPos:"auto",overflowY:"auto",closeMarkup:'<button title="%title%" type="button" class="mfp-close">&times;</button>',tClose:"Close (Esc)",tLoading:"Loading..."}},a.fn.magnificPopup=function(c){A();var d=a(this);if("string"==typeof c)if("open"===c){var e,f=u?d.data("magnificPopup"):d[0].magnificPopup,g=parseInt(arguments[1],10)||0;f.items?e=f.items[g]:(e=d,f.delegate&&(e=e.find(f.delegate)),e=e.eq(g)),b._openClick({mfpEl:e},d,f)}else b.isOpen&&b[c].apply(b,Array.prototype.slice.call(arguments,1));else c=a.extend(!0,{},c),u?d.data("magnificPopup",c):d[0].magnificPopup=c,b.addGroup(d,c);return d};var C,D,E,F="inline",G=function(){E&&(D.after(E.addClass(C)).detach(),E=null)};a.magnificPopup.registerModule(F,{options:{hiddenClass:"hide",markup:"",tNotFound:"Content not found"},proto:{initInline:function(){b.types.push(F),w(h+"."+F,function(){G()})},getInline:function(c,d){if(G(),c.src){var e=b.st.inline,f=a(c.src);if(f.length){var g=f[0].parentNode;g&&g.tagName&&(D||(C=e.hiddenClass,D=x(C),C="mfp-"+C),E=f.after(D).detach().removeClass(C)),b.updateStatus("ready")}else b.updateStatus("error",e.tNotFound),f=a("<div>");return c.inlineElement=f,f}return b.updateStatus("ready"),b._parseMarkup(d,{},c),d}}});var H,I="ajax",J=function(){H&&a(document.body).removeClass(H)},K=function(){J(),b.req&&b.req.abort()};a.magnificPopup.registerModule(I,{options:{settings:null,cursor:"mfp-ajax-cur",tError:'<a href="%url%">The content</a> could not be loaded.'},proto:{initAjax:function(){b.types.push(I),H=b.st.ajax.cursor,w(h+"."+I,K),w("BeforeChange."+I,K)},getAjax:function(c){H&&a(document.body).addClass(H),b.updateStatus("loading");var d=a.extend({url:c.src,success:function(d,e,f){var g={data:d,xhr:f};y("ParseAjax",g),b.appendContent(a(g.data),I),c.finished=!0,J(),b._setFocus(),setTimeout(function(){b.wrap.addClass(q)},16),b.updateStatus("ready"),y("AjaxContentAdded")},error:function(){J(),c.finished=c.loadError=!0,b.updateStatus("error",b.st.ajax.tError.replace("%url%",c.src))}},b.st.ajax.settings);return b.req=a.ajax(d),""}}});var L,M=function(c){if(c.data&&void 0!==c.data.title)return c.data.title;var d=b.st.image.titleSrc;if(d){if(a.isFunction(d))return d.call(b,c);if(c.el)return c.el.attr(d)||""}return""};a.magnificPopup.registerModule("image",{options:{markup:'<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>',cursor:"mfp-zoom-out-cur",titleSrc:"title",verticalFit:!0,tError:'<a href="%url%">The image</a> could not be loaded.'},proto:{initImage:function(){var c=b.st.image,d=".image";b.types.push("image"),w(m+d,function(){"image"===b.currItem.type&&c.cursor&&a(document.body).addClass(c.cursor)}),w(h+d,function(){c.cursor&&a(document.body).removeClass(c.cursor),v.off("resize"+p)}),w("Resize"+d,b.resizeImage),b.isLowIE&&w("AfterChange",b.resizeImage)},resizeImage:function(){var a=b.currItem;if(a&&a.img&&b.st.image.verticalFit){var c=0;b.isLowIE&&(c=parseInt(a.img.css("padding-top"),10)+parseInt(a.img.css("padding-bottom"),10)),a.img.css("max-height",b.wH-c)}},_onImageHasSize:function(a){a.img&&(a.hasSize=!0,L&&clearInterval(L),a.isCheckingImgSize=!1,y("ImageHasSize",a),a.imgHidden&&(b.content&&b.content.removeClass("mfp-loading"),a.imgHidden=!1))},findImageSize:function(a){var c=0,d=a.img[0],e=function(f){L&&clearInterval(L),L=setInterval(function(){return d.naturalWidth>0?void b._onImageHasSize(a):(c>200&&clearInterval(L),c++,void(3===c?e(10):40===c?e(50):100===c&&e(500)))},f)};e(1)},getImage:function(c,d){var e=0,f=function(){c&&(c.img[0].complete?(c.img.off(".mfploader"),c===b.currItem&&(b._onImageHasSize(c),b.updateStatus("ready")),c.hasSize=!0,c.loaded=!0,y("ImageLoadComplete")):(e++,200>e?setTimeout(f,100):g()))},g=function(){c&&(c.img.off(".mfploader"),c===b.currItem&&(b._onImageHasSize(c),b.updateStatus("error",h.tError.replace("%url%",c.src))),c.hasSize=!0,c.loaded=!0,c.loadError=!0)},h=b.st.image,i=d.find(".mfp-img");if(i.length){var j=document.createElement("img");j.className="mfp-img",c.el&&c.el.find("img").length&&(j.alt=c.el.find("img").attr("alt")),c.img=a(j).on("load.mfploader",f).on("error.mfploader",g),j.src=c.src,i.is("img")&&(c.img=c.img.clone()),j=c.img[0],j.naturalWidth>0?c.hasSize=!0:j.width||(c.hasSize=!1)}return b._parseMarkup(d,{title:M(c),img_replaceWith:c.img},c),b.resizeImage(),c.hasSize?(L&&clearInterval(L),c.loadError?(d.addClass("mfp-loading"),b.updateStatus("error",h.tError.replace("%url%",c.src))):(d.removeClass("mfp-loading"),b.updateStatus("ready")),d):(b.updateStatus("loading"),c.loading=!0,c.hasSize||(c.imgHidden=!0,d.addClass("mfp-loading"),b.findImageSize(c)),d)}}});var N,O=function(){return void 0===N&&(N=void 0!==document.createElement("p").style.MozTransform),N};a.magnificPopup.registerModule("zoom",{options:{enabled:!1,easing:"ease-in-out",duration:300,opener:function(a){return a.is("img")?a:a.find("img")}},proto:{initZoom:function(){var a,c=b.st.zoom,d=".zoom";if(c.enabled&&b.supportsTransition){var e,f,g=c.duration,j=function(a){var b=a.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),d="all "+c.duration/1e3+"s "+c.easing,e={position:"fixed",zIndex:9999,left:0,top:0,"-webkit-backface-visibility":"hidden"},f="transition";return e["-webkit-"+f]=e["-moz-"+f]=e["-o-"+f]=e[f]=d,b.css(e),b},k=function(){b.content.css("visibility","visible")};w("BuildControls"+d,function(){if(b._allowZoom()){if(clearTimeout(e),b.content.css("visibility","hidden"),a=b._getItemToZoom(),!a)return void k();f=j(a),f.css(b._getOffset()),b.wrap.append(f),e=setTimeout(function(){f.css(b._getOffset(!0)),e=setTimeout(function(){k(),setTimeout(function(){f.remove(),a=f=null,y("ZoomAnimationEnded")},16)},g)},16)}}),w(i+d,function(){if(b._allowZoom()){if(clearTimeout(e),b.st.removalDelay=g,!a){if(a=b._getItemToZoom(),!a)return;f=j(a)}f.css(b._getOffset(!0)),b.wrap.append(f),b.content.css("visibility","hidden"),setTimeout(function(){f.css(b._getOffset())},16)}}),w(h+d,function(){b._allowZoom()&&(k(),f&&f.remove(),a=null)})}},_allowZoom:function(){return"image"===b.currItem.type},_getItemToZoom:function(){return b.currItem.hasSize?b.currItem.img:!1},_getOffset:function(c){var d;d=c?b.currItem.img:b.st.zoom.opener(b.currItem.el||b.currItem);var e=d.offset(),f=parseInt(d.css("padding-top"),10),g=parseInt(d.css("padding-bottom"),10);e.top-=a(window).scrollTop()-f;var h={width:d.width(),height:(u?d.innerHeight():d[0].offsetHeight)-g-f};return O()?h["-moz-transform"]=h.transform="translate("+e.left+"px,"+e.top+"px)":(h.left=e.left,h.top=e.top),h}}});var P="iframe",Q="//about:blank",R=function(a){if(b.currTemplate[P]){var c=b.currTemplate[P].find("iframe");c.length&&(a||(c[0].src=Q),b.isIE8&&c.css("display",a?"block":"none"))}};a.magnificPopup.registerModule(P,{options:{markup:'<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',srcAction:"iframe_src",patterns:{youtube:{index:"youtube.com",id:"v=",src:"//www.youtube.com/embed/%id%?autoplay=1"},vimeo:{index:"vimeo.com/",id:"/",src:"//player.vimeo.com/video/%id%?autoplay=1"},gmaps:{index:"//maps.google.",src:"%id%&output=embed"}}},proto:{initIframe:function(){b.types.push(P),w("BeforeChange",function(a,b,c){b!==c&&(b===P?R():c===P&&R(!0))}),w(h+"."+P,function(){R()})},getIframe:function(c,d){var e=c.src,f=b.st.iframe;a.each(f.patterns,function(){return e.indexOf(this.index)>-1?(this.id&&(e="string"==typeof this.id?e.substr(e.lastIndexOf(this.id)+this.id.length,e.length):this.id.call(this,e)),e=this.src.replace("%id%",e),!1):void 0});var g={};return f.srcAction&&(g[f.srcAction]=e),b._parseMarkup(d,g,c),b.updateStatus("ready"),d}}});var S=function(a){var c=b.items.length;return a>c-1?a-c:0>a?c+a:a},T=function(a,b,c){return a.replace(/%curr%/gi,b+1).replace(/%total%/gi,c)};a.magnificPopup.registerModule("gallery",{options:{enabled:!1,arrowMarkup:'<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',preload:[0,2],navigateByImgClick:!0,arrows:!0,tPrev:"Previous (Left arrow key)",tNext:"Next (Right arrow key)",tCounter:"%curr% of %total%"},proto:{initGallery:function(){var c=b.st.gallery,e=".mfp-gallery",g=Boolean(a.fn.mfpFastClick);return b.direction=!0,c&&c.enabled?(f+=" mfp-gallery",w(m+e,function(){c.navigateByImgClick&&b.wrap.on("click"+e,".mfp-img",function(){return b.items.length>1?(b.next(),!1):void 0}),d.on("keydown"+e,function(a){37===a.keyCode?b.prev():39===a.keyCode&&b.next()})}),w("UpdateStatus"+e,function(a,c){c.text&&(c.text=T(c.text,b.currItem.index,b.items.length))}),w(l+e,function(a,d,e,f){var g=b.items.length;e.counter=g>1?T(c.tCounter,f.index,g):""}),w("BuildControls"+e,function(){if(b.items.length>1&&c.arrows&&!b.arrowLeft){var d=c.arrowMarkup,e=b.arrowLeft=a(d.replace(/%title%/gi,c.tPrev).replace(/%dir%/gi,"left")).addClass(s),f=b.arrowRight=a(d.replace(/%title%/gi,c.tNext).replace(/%dir%/gi,"right")).addClass(s),h=g?"mfpFastClick":"click";e[h](function(){b.prev()}),f[h](function(){b.next()}),b.isIE7&&(x("b",e[0],!1,!0),x("a",e[0],!1,!0),x("b",f[0],!1,!0),x("a",f[0],!1,!0)),b.container.append(e.add(f))}}),w(n+e,function(){b._preloadTimeout&&clearTimeout(b._preloadTimeout),b._preloadTimeout=setTimeout(function(){b.preloadNearbyImages(),b._preloadTimeout=null},16)}),void w(h+e,function(){d.off(e),b.wrap.off("click"+e),b.arrowLeft&&g&&b.arrowLeft.add(b.arrowRight).destroyMfpFastClick(),b.arrowRight=b.arrowLeft=null})):!1},next:function(){b.direction=!0,b.index=S(b.index+1),b.updateItemHTML()},prev:function(){b.direction=!1,b.index=S(b.index-1),b.updateItemHTML()},goTo:function(a){b.direction=a>=b.index,b.index=a,b.updateItemHTML()},preloadNearbyImages:function(){var a,c=b.st.gallery.preload,d=Math.min(c[0],b.items.length),e=Math.min(c[1],b.items.length);for(a=1;a<=(b.direction?e:d);a++)b._preloadItem(b.index+a);for(a=1;a<=(b.direction?d:e);a++)b._preloadItem(b.index-a)},_preloadItem:function(c){if(c=S(c),!b.items[c].preloaded){var d=b.items[c];d.parsed||(d=b.parseEl(c)),y("LazyLoad",d),"image"===d.type&&(d.img=a('<img class="mfp-img" />').on("load.mfploader",function(){d.hasSize=!0}).on("error.mfploader",function(){d.hasSize=!0,d.loadError=!0,y("LazyLoadError",d)}).attr("src",d.src)),d.preloaded=!0}}}});var U="retina";a.magnificPopup.registerModule(U,{options:{replaceSrc:function(a){return a.src.replace(/\.\w+$/,function(a){return"@2x"+a})},ratio:1},proto:{initRetina:function(){if(window.devicePixelRatio>1){var a=b.st.retina,c=a.ratio;c=isNaN(c)?c():c,c>1&&(w("ImageHasSize."+U,function(a,b){b.img.css({"max-width":b.img[0].naturalWidth/c,width:"100%"})}),w("ElementParse."+U,function(b,d){d.src=a.replaceSrc(d,c)}))}}}}),function(){var b=1e3,c="ontouchstart"in window,d=function(){v.off("touchmove"+f+" touchend"+f)},e="mfpFastClick",f="."+e;a.fn.mfpFastClick=function(e){return a(this).each(function(){var g,h=a(this);if(c){var i,j,k,l,m,n;h.on("touchstart"+f,function(a){l=!1,n=1,m=a.originalEvent?a.originalEvent.touches[0]:a.touches[0],j=m.clientX,k=m.clientY,v.on("touchmove"+f,function(a){m=a.originalEvent?a.originalEvent.touches:a.touches,n=m.length,m=m[0],(Math.abs(m.clientX-j)>10||Math.abs(m.clientY-k)>10)&&(l=!0,d())}).on("touchend"+f,function(a){d(),l||n>1||(g=!0,a.preventDefault(),clearTimeout(i),i=setTimeout(function(){g=!1},b),e())})})}h.on("click"+f,function(){g||e()})})},a.fn.destroyMfpFastClick=function(){a(this).off("touchstart"+f+" click"+f),c&&v.off("touchmove"+f+" touchend"+f)}}(),A()});
( function ( $ ) {

    /* DOM Ready */
    $( 'document' ).ready( function () {
        siteInitLogin();
        siteInitRegister();
        lostPassword();
    } );

    /* Login popup */
    function siteInitLogin() {

        /* Initialize Popup */
        onesocialModalPopup( '.onesocial-login-popup-link', '.mfp-content .LoginBox' );

        /* This will open popup on each link of login page */
        $( '.login.header-button' ).click( function ( e ) {
            e.preventDefault();
            $( '.onesocial-login-popup-link' ).trigger( 'click' );
        } );

        $( '#login_button' ).on( 'click', function () {

            var ele_this = $( this );

            /* Show Spinner */
            ele_this.prop( 'disabled', true ).find( '.fa-spin' ).show();

            //Requesting fresh nonce
            $.post( ajaxurl, { action: 'onesocial_create_nonce' }, function( nonce ){

                var dologin = $.post( transport.eh_url_path + "/?ajax-login",
                    {
                        'ajax-login-security': nonce,
                        'username': $( "#login_username" ).val(),
                        "password": $( "#login_password" ).val(),
                        "rememberme": $( "#login_rememberme" ).val()
                    }
                );

                /* Login Done Event */
                dologin.done( function ( d ) {
                    ele_this.prop( 'disabled', false ).find( '.fa-spin' ).hide();
                    $.globalEval( d );
                } );

            });

        });

        /* Open Register Form */
        $( '.joinbutton' ).on( 'click', function ( e ) {
            e.preventDefault();

            setTimeout( function () {
                $( '.onesocial-register-popup-link' ).trigger( 'click' );
                oneSocialTinyMceFix();
            }, 90 );

            setTimeout( function () {
                $( 'body' ).find( '.mfp-content .RegisterBox' ).addClass( 'go' );
            }, 300 );

        } );

        /* On Enter tirggerd submit login form */
        $( '#siteLoginBox' ).find( 'input' ).keypress( function ( event ) {
            if ( event.which === 13 ) {
                event.preventDefault();
                $( '#login_button' ).click();
            }
        } );
    }

    /* Register popup */
    function siteInitRegister() {

        /* Initialize Popup */
        onesocialModalPopup( '.onesocial-register-popup-link', '.mfp-content .RegisterBox' );

        /* This will open popup on each link of login page */
        $( '.register.header-button' ).click( function ( e ) {
            e.preventDefault();

            setTimeout( function () {
                $( '.onesocial-register-popup-link' ).trigger( 'click' );
                oneSocialTinyMceFix();
            }, 90 );

        } );

        /*$( '#register_button' ).click( function () {

         var ele_this = $( this );

         // Show Spinner
         ele_this.prop( 'disabled', true ).find( '.fa-spin' ).show();

         var $to_send = {
         'ajax-register-security': $( "#ajax-register-security" ).val(),
         'email': $( "#register_email" ).val(),
         'username': $( "#register_username" ).val(),
         "password": $( "#register_password" ).val()
         };

         if($("#as_vendor").length && $("#as_vendor").attr("checked")) {
         $to_send['as_vendor'] = 'true';
         } else {
         $to_send['as_vendor'] = 'false';
         }

         var dologin = $.post( "?ajax-register", $to_send);

         // Login Done Event
         dologin.done( function ( d ) {
         ele_this.prop( 'disabled', false ).find( '.fa-spin' ).hide();
         $.globalEval( d );
         } );

         } );*/

        $( '#register_okay' ).click( function () {
            location.reload();
        } );

        /* Open Login Form */
        $( '.siginbutton' ).click( function ( e ) {
            e.preventDefault();

            setTimeout( function () {
                $( '.onesocial-login-popup-link' ).trigger( 'click' );
            }, 90 );

            setTimeout( function () {
                $( 'body' ).find( '.mfp-content .LoginBox' ).addClass( 'go' );
            }, 300 );

        } );

        /*$( '#register_password, #register_email, #register_username' ).bind( "enterKey", function ( e ) {
         $( "#register_button" ).trigger( 'click' );
         } );

         $( '#register_password, #register_email, #register_username' ).keyup( function ( e ) {
         if ( e.keyCode === 13 ) {
         $( this ).trigger( "enterKey" );
         }
         } );*/

        if ( $('#siteRegisterBox #blog-details-section').length != 0 ) {
            var blog_checked = $('#siteRegisterBox #signup_with_blog');

            // hide "Blog Details" block if not checked by default
            if ( ! blog_checked.prop('checked') ) {
                $('#siteRegisterBox #blog-details').toggle();
            }

            // toggle "Blog Details" block whenever checkbox is checked
            blog_checked.change(function() {
                $('#siteRegisterBox #blog-details').toggle();
            });
        }

        var frm_siteRegister_ajaxform_options = {
            dataType: 'json',
            currentform: false,
            beforeSubmit: function ( arr, $form, options ) {
                currentform = $form;

                $form.find( '.field_erorr' ).remove();
                /* Show spinner */
                $form.find( '[type="submit"] .fa-spin' ).show();
                $( '#ajax_register_messages' ).html( '' );
            },
            success: function ( response ) {
                /**
                 show success/error message
                 hide loading animation
                 check if success
                 - replace content
                 execute javascript
                 */
                if ( response.message ) {
                    var cssclass = 'ctmessage updated';
                    if ( !response.success ) {
                        cssclass = 'ctmessage error';
                    }

                    $( '#ajax_register_messages' ).html( "<p class='" + cssclass + "'>" + response.message + "</p>" );
                }

                currentform.find( '[type="submit"] .fa-spin' ).hide();
                if ( response.success ) {
                    currentform.find( '.boxcontent' ).html( response.template );
                }

                if ( response.js ) {
                    $.globalEval( response.js );
                }

            },
            error: function ( jqXHR, textStatus, errorThrown ) {
                $( '#ajax_register_messages' ).html( "<p class='alert alert-error'>Error!</p>" );
            }
        };

        $( '#frm_siteRegisterBox' ).ajaxForm( frm_siteRegister_ajaxform_options );

    }

    /* For Lost Password */
    function lostPassword() {

        /* Initialize Popup */
        onesocialModalPopup( '.onesocial-lost-password-popup-link', '.mfp-content .LostPasswordBox' );

        /* This will open popup on each link of login page */
        $( document ).on( 'click', '.boss-modal-form .forgetme, #ajax_login_messages a[href$="lost-password/"]', function ( e ) {
            e.preventDefault();
            $( '.onesocial-lost-password-popup-link' ).trigger( 'click' );

            setTimeout( function () {
                $( 'body' ).find( '.mfp-content .LostPasswordBox' ).addClass( 'go' );
            }, 300 );
        } );

        $( '#lost-pass-button' ).on( 'click', function ( e ) {
            e.preventDefault();

            var message = $( '#siteLostPassword #message' ),
                contents = {
                    action: 'lost_pass',
                    nonce: $( '#rs_user_lost_password_nonce' ).val(),
                    user_login: $( '#user_login' ).val()
                };

            // disable button onsubmit to avoid double submision
            $( this ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

            // Display our pre-loading
            $( this ).find( '.fa' ).fadeIn();

            $.post( ajaxurl, contents, function ( data ) {
                $( '#lost-pass-button' ).removeAttr( 'disabled' ).removeClass( 'disabled' );

                // hide pre-loader
                $( '#lost-pass-button' ).find( '.fa' ).fadeOut();

                // display return data
                message.html( data );
            } );

            return false;
        } );
    }

    /* Initialize Modal Popup */
    var startWindowScroll = 0;
    function onesocialModalPopup( elem, modal ) {
        $( elem ).magnificPopup( {
            type: 'inline',
            closeOnBgClick: false,
            fixedContentPos: true,
            //fixedBgPos: true,
            closeMarkup: '<button title="%title%" class="mfp-close bb-icon-close"></button>',
            //removalDelay: 400, //delay removal by X to allow out-animation,
            callbacks: {
                beforeOpen: function () {
                    this.st.mainClass = 'onesocial-popup';
                    $('html').addClass('mfp-helper');
                    $('#main-wrap').css('height', '0');
                    startWindowScroll = $(window).scrollTop();
                },
                open: function () {
                    $( 'body' ).addClass( 'popup-open' );
                    setTimeout( function () {
                        $( 'body' ).find( modal ).addClass( 'go' );
                    }, 300 );
                },
                close: function () {
                    $( 'body' ).removeClass( 'popup-open' );
                    $('html').removeClass('mfp-helper');
                    $('#main-wrap').css('height', '');
                    $(window).scrollTop(startWindowScroll);
                }
            }
        } );
    }

    //Fix: WP_EDITOR on Frontend in modal doesn't work properly
    function oneSocialTinyMceFix() {
        if ( typeof tinyMCE == 'undefined' )
            return;

        try {
            $( '.field_type_textarea' ).each( function () {
                var id = $( this ).find( 'textarea' ).attr( 'id' );
                tinymce.remove( '#' + id );
                tinyMCE.execCommand( 'mceToggleEditor', false, id );
            });
        } catch (e) {

        }

    }

}( jQuery ) );
( function ( $ ) {

    $( document ).on( 'click', '.button-load-more-posts', function ( event ) {
        event.preventDefault();

        var self = $( this );

        var page = self.data( 'page' ),
            template = self.data( 'template' ),
            href = self.attr( 'href' );

        self.addClass( 'loading' );

        $.get( href, function ( response ) {
            $( '.pagination-below' ).remove();

            if ( template === 'home' ) {
                $( response ).find( '.article-outher' ).each( function () {
                    $( '#content' ).append( $( this ) );
                } );
            } else if ( template === 'search' ) {
                $( response ).find( 'article.hentry' ).each( function () {
                    $( '.search-content-inner' ).append( $( this ) );
                } );
            } else {
                $( response ).find( '.article-outher' ).each( function () {
                    $( '#content' ).append( $( this ) );
                } );
            }

            $( '#content' ).append( $( response ).find( '.pagination-below' ) );
        } );
    } );

    $( document ).on( 'scroll', function () {

        var load_more_posts = $( '.post-infinite-scroll' );

        if ( load_more_posts.length ) {

            var pos = load_more_posts.offset();

            if ( $( window ).scrollTop() + $( window ).height() > pos.top ) {

                if ( !load_more_posts.hasClass( 'loading' ) ) {
                    load_more_posts.trigger( 'click' );
                }
            }
        }

    } );

} )( jQuery );
// Sticky Plugin v1.0.3 for jQuery
// =============
// Author: Anthony Garand
// Improvements by German M. Bravo (Kronuz) and Ruud Kamphuis (ruudk)
// Improvements by Leonardo C. Daronco (daronco)
// Created: 02/14/2011
// Date: 07/20/2015
// Website: http://stickyjs.com/
// Description: Makes an element on the page stick on the screen as you scroll
//              It will only set the 'top' and 'position' of your element, you
//              might need to adjust the width in some cases.

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    var slice = Array.prototype.slice; // save ref to original slice()
    var splice = Array.prototype.splice; // save ref to original slice()

  var defaults = {
      topSpacing: 0,
      bottomSpacing: 0,
      className: 'is-sticky',
      wrapperClassName: 'sticky-wrapper',
      center: false,
      getWidthFrom: '',
      widthFromWrapper: true, // works only when .getWidthFrom is empty
      responsiveWidth: false
    },
    $window = $(window),
    $document = $(document),
    sticked = [],
    windowHeight = $window.height(),
    scroller = function() {
      var scrollTop = $window.scrollTop(),
        documentHeight = $document.height(),
        dwh = documentHeight - windowHeight,
        extra = (scrollTop > dwh) ? dwh - scrollTop : 0;

      for (var i = 0, l = sticked.length; i < l; i++) {
        var s = sticked[i],
          elementTop = s.stickyWrapper.offset().top,
          etse = elementTop - s.topSpacing - extra;

        //update height in case of dynamic content
        s.stickyWrapper.css('height', s.stickyElement.outerHeight());

        if (scrollTop <= etse) {
          if (s.currentTop !== null) {
            s.stickyElement
              .css({
                'width': '',
                'position': '',
                'top': ''
              });
            s.stickyElement.parent().removeClass(s.className);
            s.stickyElement.trigger('sticky-end', [s]);
            s.currentTop = null;
          }
        }
        else {
          var newTop = documentHeight - s.stickyElement.outerHeight()
            - s.topSpacing - s.bottomSpacing - scrollTop - extra;
          if (newTop < 0) {
            newTop = newTop + s.topSpacing;
          } else {
            newTop = s.topSpacing;
          }
          if (s.currentTop !== newTop) {
            var newWidth;
            if (s.getWidthFrom) {
                newWidth = $(s.getWidthFrom).width() || null;
            } else if (s.widthFromWrapper) {
                newWidth = s.stickyWrapper.width();
            }
            if (newWidth == null) {
                newWidth = s.stickyElement.width();
            }
            s.stickyElement
              .css('width', newWidth)
              .css('position', 'fixed')
              .css('top', newTop);

            s.stickyElement.parent().addClass(s.className);

            if (s.currentTop === null) {
              s.stickyElement.trigger('sticky-start', [s]);
            } else {
              // sticky is started but it have to be repositioned
              s.stickyElement.trigger('sticky-update', [s]);
            }

            if (s.currentTop === s.topSpacing && s.currentTop > newTop || s.currentTop === null && newTop < s.topSpacing) {
              // just reached bottom || just started to stick but bottom is already reached
              s.stickyElement.trigger('sticky-bottom-reached', [s]);
            } else if(s.currentTop !== null && newTop === s.topSpacing && s.currentTop < newTop) {
              // sticky is started && sticked at topSpacing && overflowing from top just finished
              s.stickyElement.trigger('sticky-bottom-unreached', [s]);
            }

            s.currentTop = newTop;
          }

          // Check if sticky has reached end of container and stop sticking
          var stickyWrapperContainer = s.stickyWrapper.parent();
          var unstick = (s.stickyElement.offset().top + s.stickyElement.outerHeight() >= stickyWrapperContainer.offset().top + stickyWrapperContainer.outerHeight()) && (s.stickyElement.offset().top <= s.topSpacing);

          if( unstick ) {
            s.stickyElement
              .css('position', 'absolute')
              .css('top', '')
              .css('bottom', 0);
          } else {
            s.stickyElement
              .css('position', 'fixed')
              .css('top', newTop)
              .css('bottom', '');
          }
        }
      }
    },
    resizer = function() {
      windowHeight = $window.height();

      for (var i = 0, l = sticked.length; i < l; i++) {
        var s = sticked[i];
        var newWidth = null;
        if (s.getWidthFrom) {
            if (s.responsiveWidth) {
                newWidth = $(s.getWidthFrom).width();
            }
        } else if(s.widthFromWrapper) {
            newWidth = s.stickyWrapper.width();
        }
        if (newWidth != null) {
            s.stickyElement.css('width', newWidth);
        }
      }
    },
    methods = {
      init: function(options) {
        var o = $.extend({}, defaults, options);
        return this.each(function() {
          var stickyElement = $(this);

          var stickyId = stickyElement.attr('id');
          var wrapperId = stickyId ? stickyId + '-' + defaults.wrapperClassName : defaults.wrapperClassName;
          var wrapper = $('<div></div>')
            .attr('id', wrapperId)
            .addClass(o.wrapperClassName);

          stickyElement.wrapAll(wrapper);

          var stickyWrapper = stickyElement.parent();

          if (o.center) {
            stickyWrapper.css({width:stickyElement.outerWidth(),marginLeft:"auto",marginRight:"auto"});
          }

          if (stickyElement.css("float") === "right") {
            stickyElement.css({"float":"none"}).parent().css({"float":"right"});
          }

          o.stickyElement = stickyElement;
          o.stickyWrapper = stickyWrapper;
          o.currentTop    = null;

          sticked.push(o);

          methods.setWrapperHeight(this);
          methods.setupChangeListeners(this);
        });
      },

      setWrapperHeight: function(stickyElement) {
        var element = $(stickyElement);
        var stickyWrapper = element.parent();
        if (stickyWrapper) {
          stickyWrapper.css('height', element.outerHeight());
        }
      },

      setupChangeListeners: function(stickyElement) {
        if (window.MutationObserver) {
          var mutationObserver = new window.MutationObserver(function(mutations) {
            if (mutations[0].addedNodes.length || mutations[0].removedNodes.length) {
              methods.setWrapperHeight(stickyElement);
            }
          });
          mutationObserver.observe(stickyElement, {subtree: true, childList: true});
        } else {
          stickyElement.addEventListener('DOMNodeInserted', function() {
            methods.setWrapperHeight(stickyElement);
          }, false);
          stickyElement.addEventListener('DOMNodeRemoved', function() {
            methods.setWrapperHeight(stickyElement);
          }, false);
        }
      },
      update: scroller,
      unstick: function(options) {
        return this.each(function() {
          var that = this;
          var unstickyElement = $(that);

          var removeIdx = -1;
          var i = sticked.length;
          while (i-- > 0) {
            if (sticked[i].stickyElement.get(0) === that) {
                splice.call(sticked,i,1);
                removeIdx = i;
            }
          }
          if(removeIdx !== -1) {
            unstickyElement.unwrap();
            unstickyElement
              .css({
                'width': '',
                'position': '',
                'top': '',
                'float': ''
              })
            ;
          }
        });
      }
    };

  // should be more efficient than using $window.scroll(scroller) and $window.resize(resizer):
  if (window.addEventListener) {
    window.addEventListener('scroll', scroller, false);
    window.addEventListener('resize', resizer, false);
  } else if (window.attachEvent) {
    window.attachEvent('onscroll', scroller);
    window.attachEvent('onresize', resizer);
  }

  $.fn.sticky = function(method) {
    if (methods[method]) {
      return methods[method].apply(this, slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error('Method ' + method + ' does not exist on jQuery.sticky');
    }
  };

  $.fn.unstick = function(method) {
    if (methods[method]) {
      return methods[method].apply(this, slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method ) {
      return methods.unstick.apply( this, arguments );
    } else {
      $.error('Method ' + method + ' does not exist on jQuery.sticky');
    }
  };
  $(function() {
    setTimeout(scroller, 0);
  });
}));
( function ( $ ) {

    var isTouch = !!( 'ontouchstart' in window ),
        TorC = isTouch ? 'touchstart' : 'click';

    $( '#main-nav' ).on( TorC, function ( e ) {
        e.preventDefault();

        var $body = $( 'body' ),
            $page = $( '#main-wrap' ),
            transitionEndNav = 'transitionend webkitTransitionEnd otransitionend MSTransitionEnd';

        $( '#mobile-right-panel' ).css( { 'opacity': 1 } );

        $page.on( transitionEndNav, function () {
            if ( !$( 'body' ).hasClass( 'menu-visible-right' ) ) {
                $( '#mobile-right-panel' ).removeAttr( 'style' );
                $page.off( transitionEndNav );
            }
        } );

        /* When the toggle menu link is clicked, animation starts */
        $body.toggleClass( 'menu-visible-right' );


    } );

    $( '#user-nav' ).on( TorC, function ( e ) {
        e.preventDefault();

        var $body = $( 'body' ),
            $page = $( '#main-wrap' ),
            /* Cross browser support for CSS "transition end" event */
            transitionEndNav = 'transitionend webkitTransitionEnd otransitionend MSTransitionEnd';

        $( '#wpadminbar' ).css( { 'opacity': 1 } );

        $page.on( transitionEndNav, function () {
            if ( !$( 'body' ).hasClass( 'menu-visible-left' ) ) {
                $( '#wpadminbar' ).removeAttr( 'style' );
                $page.off( transitionEndNav );
            }
        } );

        /* When the toggle menu link is clicked, animation starts */
        $body.toggleClass( 'menu-visible-left' );

    } );

    $( document ).on( 'ready', function () {

        $( '.bb-overlay' ).on( 'click', function () {
            if ( $( 'body' ).hasClass( 'menu-visible-right' ) ) {
                $( '#main-nav' ).trigger( TorC );
            }

            if ( $( 'body' ).hasClass( 'menu-visible-left' ) ) {
                $( '#user-nav' ).trigger( TorC );
            }
        } );

    } );

    //Fix - mobile left panel links  to be clicked twice
    jQuery("#wpadminbar.mobile").find("#wp-admin-bar-my-account.menupop.with-avatar a.ab-item").on("click.wp-mobile-hover", function(e) {
        e.stopPropagation();
    });

} )( jQuery );
( function ( $ ) {
    // Get member blog posts on blog page
    $( document ).on( 'click', '.load-more-posts a', function ( event ) {
        event.preventDefault();

        var $link = $( this ),
            author_id = $link.attr( 'href' ),
            data_target = $link.attr( 'data-target' ),
            sort = $link.data( 'sort' ),
            $parent = $link.parent( '.load-more-posts' );
        $container = $link.parents( '.article-outher' ).find( '.posts-stream' );

        $parent.addClass( 'active' );

        if ( $parent.hasClass( 'loaded' ) ) {
            $container.html( '<div class="loader">Loading...</div>' );
        }

        $container.next( '.header-area' ).hide();
        $container.next().next().hide();
        $container.fadeIn();
        $.ajax( {
            url: ajaxposts.ajaxurl,
            type: 'post',
            data: {
                action: 'buddyboss_ajax_posts',
                author: author_id,
                data_target: data_target,
                sort: sort,
                page: 'blog',
                postsNonce: ajaxposts.postsNonce
            },
            success: function ( html ) {
                $container.find( '.loader' ).fadeOut();
                $container.html( html );
                $container.find( '.inner' ).fadeIn();
                $parent.addClass( 'loaded' );
//                $( 'html,body' ).animate( { scrollTop: $( document ).scrollTop().valueOf() + 1 } );

                setTimeout( function () {
                    animatePosts( $link );
                }, 90 );

            }
        } );
    } );

    $( document ).on( 'click', '.load-more-posts.loaded i', function ( event ) {
        var $link = $( this ),
            $anchor = $( this ).next(),
            $parent = $link.parent( '.load-more-posts' ),
            $container = $link.parents( '.article-outher' ).find( '.posts-stream' );

        setTimeout( function () {
            animatePosts( $anchor );
        }, 500 );


        $parent.toggleClass( 'active' );
        $container.next( '.header-area' ).toggle();
        $container.next().next().toggle();
        $container.toggle();
    } );


} )( jQuery );


function animatePosts( $link ) {
    var target = $link.attr( 'data-target' );
    if ( $link.attr( 'data-sequence' ) !== undefined ) {
        var firstId = $( "." + target + ":first" ).attr( 'data-id' );
        var lastId = $( "." + target + ":last" ).attr( 'data-id' );
        var number = firstId;

        //Add or remove the class
        if ( $( "." + target + "[data-id=" + number + "]" ).hasClass( 'go' ) ) {
            $( "." + target + "[data-id=" + number + "]" ).addClass( 'goAway' );
            $( "." + target + "[data-id=" + number + "]" ).removeClass( 'go' );
        } else {
            $( "." + target + "[data-id=" + number + "]" ).addClass( 'go' );
            $( "." + target + "[data-id=" + number + "]" ).removeClass( 'goAway' );
        }
        number++;
        delay = Number( $link.attr( 'data-sequence' ) );
        $.doTimeout( delay, function () {
            //console.log( lastId );

            //Add or remove the class
            if ( $( "." + target + "[data-id=" + number + "]" ).hasClass( 'go' ) ) {
                $( "." + target + "[data-id=" + number + "]" ).addClass( 'goAway' );
                $( "." + target + "[data-id=" + number + "]" ).removeClass( 'go' );
            } else {
                $( "." + target + "[data-id=" + number + "]" ).addClass( 'go' );
                $( "." + target + "[data-id=" + number + "]" ).removeClass( 'goAway' );
            }

            //increment
            ++number;

            //continute looping till reached last ID
            if ( number <= lastId ) {
                return true;
            }
        } );
    }
}
/**
 * BuddyBoss JavaScript functionality
 *
 * @since    3.0
 * @package  buddyboss
 *
 * ====================================================================
 *
 * 1. jQuery Global
 * 2. Main BuddyBoss Class
 * 3. Inline Plugins
 */


/**
 * 1. jQuery Global
 * ====================================================================
 */
var jq = $ = jQuery;

/*--------------------------------------------------------------------------------------------------------
Group Load Scroll
 --------------------------------------------------------------------------------------------------------*/
$(document).ready(function($) {
    $( '.single-item #buddypress' ).attr( 'style', '');
    $body = $('body');
    if ( $body.hasClass( 'single-item' ) && $body.hasClass( 'groups' ) && $body.scrollTop() == 0 && $( '#buddypress' ).data( 'cover-size' ) !== 200 && !$( '#buddypress' ).data( 'page-refreshed' ) ) {

        $( "html,body" ).scrollTop( 260 );
    }
});
/**
 * 2. Main BuddyBoss Class
 *
 * This class takes care of BuddyPress additional functionality and
 * provides a global name space for BuddyBoss plugins to communicate
 * through.
 *
 * Event name spacing:
 * $(document).on( "buddyboss:*module*:*event*", myCallBackFunction );
 * $(document).trigger( "buddyboss:*module*:*event*", [a,b,c]/{k:v} );
 * ====================================================================
 * @return {class}
 */
var BuddyBossMain = ( function ( $, window, undefined ) {

    /**
     * Globals/Options
     */
    var _l = {
        $document: $( document ),
        $window: $( window )
    };

    // Controller
    var App = { };

    // Custom Events
    var Vent = $( { } );

    // Responsive
    var Responsive = { };

    // BuddyPress Defaults
    var BuddyPress = { };

    // BuddyPress Legacy
    var BP_Legacy = { };


    /** --------------------------------------------------------------- */

    /**
     * Application
     */

    // Initialize, runs when script is processed/loaded
    App.init = function () {

        _l.$document.ready( App.domReady );

        BP_Legacy.init();
    }

    // When the DOM is ready (page laoded)
    App.domReady = function () {
        _l.body = $( 'body' );
        _l.$buddypress = $( '#buddypress' );

        Responsive.domReady();
    }

    /** --------------------------------------------------------------- */

    /**
     * BuddyPress Responsive Help
     */
    Responsive.domReady = function () {

        // GLOBALS *
        // ---------
        window.BuddyBoss = window.BuddyBoss || { };

        window.BuddyBoss.is_mobile = null;

        var
            $document = $( document ),
            $window = $( window ),
            $body = $( 'body' ),
            mobile_width = 720,
            is_mobile = false,
            has_item_nav = false,
            mobile_modified = false,
            swiper = false,
            $main = $( '#main-wrap' ),
            $inner = $( '#inner-wrap' ),
            $buddypress = $( '#buddypress' ),
            $item_nav = $buddypress.find( '#item-nav' ),
            Panels = { },
            inputsEnabled = $( 'body' ).data( 'inputs' ),
            $mobile_nav_wrap,
            $mobile_item_wrap,
            $mobile_item_nav;

        // Detect android stock browser
        // http://stackoverflow.com/a/17961266
        var isAndroid = navigator.userAgent.indexOf( 'Android' ) >= 0;
        var webkitVer = parseInt( ( /WebKit\/([0-9]+)/.exec( navigator.appVersion ) || 0 )[1], 10 ) || void 0; // also match AppleWebKit
        var isNativeAndroid = isAndroid && webkitVer <= 534 && navigator.vendor.indexOf( 'Google' ) == 0;

        /*------------------------------------------------------------------------------------------------------
         1.0 - Core Functions
         --------------------------------------------------------------------------------------------------------*/

        // get viewport size
        function viewport() {
            var e = window, a = 'inner';
            if ( !( 'innerWidth' in window ) ) {
                a = 'client';
                e = document.documentElement || document.body;
            }
            return { width: e[ a + 'Width' ], height: e[ a + 'Height' ] };
        }
        /**
         * Checks for supported mobile resolutions via media query and
         * maximum window width.
         *
         * @return {boolean} True when screen size is mobile focused
         */
        function check_is_mobile() {
            // The $mobile_check element refers to an empty div#mobile-check we
            // hide or show with media queries. We use this to determine if we're
            // on mobile resolution

//                mobile_css = window.document.getElementById('boss-main-mobile-css'),
//                $mobile_css = $(mobile_css);

            if ( $.cookie( 'switch_mode' ) != 'mobile' ) {
//                    if(($mobile_css.attr('media') != 'all')) {
                if ( ( !translation.only_mobile ) ) {
                    if ( viewport().width <= 1024 ) {
                        $( 'body' ).removeClass( 'is-desktop' ).addClass( 'is-mobile' );
                    } else {
                        $( 'body' ).removeClass( 'is-mobile' ).addClass( 'is-desktop' );
                    }
                }
            }

            is_mobile = BuddyBoss.is_mobile = $( 'body' ).hasClass( 'is-mobile' );

            return is_mobile;
        }

        /**
         * Checks for a BuddyPress sub-page menu. On smaller screens we turn
         * this into a left/right swiper
         *
         * @return {boolean} True when BuddyPress item navigation exists
         */
        function check_has_item_nav() {
            if ( $item_nav && $item_nav.length ) {
                has_item_nav = true;
            }

            return has_item_nav;
        }

        function render_layout() {
            var
                window_height = $window.height(), // window height - 60px (Header height) - carousel_nav_height (Carousel Navigation space)
                carousel_width = ( $item_nav.find( 'li' ).length * 94 );

            // If on small screens make sure the main page elements are
            // full width vertically
            if ( is_mobile && ( $inner.height() < $window.height() ) ) {
                $( '#page' ).css( 'min-height', $window.height() - ( $( '#mobile-header' ).height() + $( '#colophon' ).height() ) );
            }

            // Swipe/panel shut area
            if ( is_mobile && $( '#buddyboss-swipe-area' ).length && Panels.state ) {
                $( '#buddyboss-swipe-area' ).css( {
                    left: Panels.state === 'left' ? 240 : 'auto',
                    right: Panels.state === 'right' ? 240 : 'auto',
                    width: $( window ).width() - 240,
                    height: $( window ).outerHeight( true ) + 200
                } );
            }

            // Mobile "My Profile" nav
            if ( is_mobile ) {
                if ( 0 !== $('#menu-my-profile').length ) {
                    var myProfileMenu = '<li id="wp-admin-bar-my-account-settings" class="menupop"><a class="ab-item" aria-haspopup="true" href="#">'+translation.other+'</a><div class="ab-sub-wrapper">'+$('#menu-my-profile')[0].outerHTML+'</div><li>';
                    $(myProfileMenu).appendTo('#wp-admin-bar-my-account-buddypress');
                }
            }

            // Log out link in left panel
            var $left_logout_link = $( '#wp-admin-bar-logout' ),
                $left_account_panel = $( '#wp-admin-bar-user-actions' ),
                $left_settings_menu = $( '#wp-admin-bar-my-account-settings .ab-submenu' ).first();

            if ( $left_logout_link.length && $left_account_panel.length && $left_settings_menu.length ) {
                // On mobile user's accidentally click the link when it's up
                // top so we move it into the setting menu
                if ( is_mobile ) {
                    $left_logout_link.appendTo( $left_settings_menu );
                }
                // On desktop we move it back to it's original place
                else {
                    $left_logout_link.appendTo( $left_account_panel );
                }
            }

            // Runs once, first time we experience a mobile resolution
            if ( is_mobile && has_item_nav && !mobile_modified ) {
                mobile_modified = true;
                $mobile_nav_wrap = $( '<div id="mobile-item-nav-wrap" class="mobile-item-nav-container mobile-item-nav-scroll-container">' );
                $mobile_item_wrap = $( '<div class="mobile-item-nav-wrapper">' ).appendTo( $mobile_nav_wrap );
                $mobile_item_nav = $( '<div id="mobile-item-nav" class="mobile-item-nav">' ).appendTo( $mobile_item_wrap );
                $mobile_item_nav.append( $item_nav.html() );

                $mobile_item_nav.css( 'width', ( $item_nav.find( 'li' ).length * 94 ) );
                $mobile_nav_wrap.insertBefore( $item_nav ).show();
                $( '#mobile-item-nav-wrap, .mobile-item-nav-scroll-container, .mobile-item-nav-container' ).addClass( 'fixed' );
                $item_nav.css( { display: 'none' } );
            }
            // Resized to non-mobile resolution
            else if ( !is_mobile && has_item_nav && mobile_modified ) {
                $mobile_nav_wrap.css( { display: 'none' } );
                $item_nav.css( { display: 'block' } );
                $document.trigger( 'menu-close.buddyboss' );
            }
            // Resized back to mobile resolution
            else if ( is_mobile && has_item_nav && mobile_modified ) {
                $mobile_nav_wrap.css( {
                    display: 'block',
                    width: carousel_width
                } );

                $mobile_item_nav.css( {
                    width: carousel_width
                } );

                $item_nav.css( { display: 'none' } );
            }

            // Update select drop-downs
            if ( typeof Selects !== 'undefined' ) {
                if ( $.isFunction( Selects.populate_select_label ) ) {
                    Selects.populate_select_label( is_mobile );
                }
            }
        }

        /**
         * Renders the layout, called when the page is loaded and on resize
         *
         * @return {void}
         */
        function do_render()
        {
            check_is_mobile();
            check_has_item_nav();
            render_layout();
            mobile_carousel();
        }

        /*------------------------------------------------------------------------------------------------------
         1.1 - Startup (Binds Events + Conditionals)
         --------------------------------------------------------------------------------------------------------*/

        // Render layout
        do_render();

        // Re-render layout after everything's loaded
        $window.bind( 'load', function () {
            do_render();
            init_mobile_my_profile_menu();
        } );

        // Mobile "My Profile" nav
        function init_mobile_my_profile_menu() {

            if ( is_mobile ) {
                if ( 0 !== $('#menu-my-profile').length ) {
                    var myProfileMenu = '<li id="wp-admin-bar-my-account-settings" class="menupop"><a class="ab-item" aria-haspopup="true" href="#">'+translation.other+'</a><div class="ab-sub-wrapper">'+$('#menu-my-profile')[0].outerHTML+'</div><li>';
                    $(myProfileMenu).appendTo('#wp-admin-bar-my-account-buddypress');
                    //Fix - mobile left panel links  to be clicked twice
                    jQuery("#wpadminbar.mobile").find("#menu-my-profile a").on("click.wp-mobile-hover", function(e) {
                        e.stopPropagation();
                    });
                }
            }
        }

        // Re-render layout on resize
        var throttle;
        $window.resize( function () {
            clearTimeout( throttle );
            throttle = setTimeout( do_render, 150 );
        } );

        function elementInViewport( el ) {
            var top = el.offsetTop;
            var left = el.offsetLeft;
            var width = el.offsetWidth;
            var height = el.offsetHeight;

            while ( el.offsetParent ) {
                el = el.offsetParent;
                top += el.offsetTop;
                left += el.offsetLeft;
            }

            return (
                top < ( window.pageYOffset + window.innerHeight ) &&
                left < ( window.pageXOffset + window.innerWidth ) &&
                ( top + height ) > window.pageYOffset &&
                ( left + width ) > window.pageXOffset
                );
        }

        var $images_out_of_viewport =
            $( '#main-wrap img, .svg-graphic' ).filter( function ( index ) {
            return !elementInViewport( this );
        } );

        $images_out_of_viewport.each( function () {
            this.classList.add( "not-loaded" );
        } );


        var waypoints = $images_out_of_viewport
            .waypoint( {
                handler: function ( direction ) {
                    if ( this.element != undefined ) {
                        this.element.classList.add( "loaded" );
                    } else {
                        //fallback
                        this.classList.add( "loaded" );
                    }
                },
                offset: '85%'
            } );

        $( window ).trigger( 'scroll' );

        $( document ).ajaxSuccess( function () {
            setTimeout( function () {
                Waypoint.refreshAll();
                waypoints = $( '#main-wrap img.not-loaded, .svg-graphic' )
                    .waypoint( {
                        handler: function ( direction ) {
                            if ( this.element !== undefined ) {
                                this.element.classList.add( "loaded" );
                            }
                        },
                        offset: '85%'
                    } );
            }, 200 );
        } );

        /*------------------------------------------------------------------------------------------------------
         1.9 - Group Avatar upload fix
         --------------------------------------------------------------------------------------------------------*/
        $( document ).ajaxSuccess( function ( event, request, settings ) {
            if ( $( 'body' ).hasClass( 'groups' ) && event.currentTarget.URL.indexOf( 'admin/group-avatar' ) > -1 && request.responseJSON && request.responseJSON.data && request.responseJSON.data.avatar ) {
                $( '#item-header-avatar image' ).attr( 'xlink:href', request.responseJSON.data.avatar );
            }
        } );

        $( document ).ajaxSuccess( function ( event, request, settings ) {
            if ( $( 'body' ).hasClass( 'group-create' ) && event.currentTarget.URL.indexOf( 'step/group-avatar' ) > -1 && request.responseJSON && request.responseJSON.data && request.responseJSON.data.avatar ) {
                $( '.left-menu img' ).attr( 'src', request.responseJSON.data.avatar );
            }
        } );

        /*------------------------------------------------------------------------------------------------------
         2.0 - Responsive Menus
         --------------------------------------------------------------------------------------------------------*/
//        $( '#main-nav' ).on( 'click', function ( e ) {
//            e.preventDefault();
//            $( 'body' ).toggleClass( 'open-right' );
//        } );
//
//        $( '#user-nav' ).on( 'click', function ( e ) {
//            e.preventDefault();
//            $( 'body' ).toggleClass( 'open-left' );
//        } );
//
//        $( document ).mouseup( function ( e ) {
//            var container = $( '#mobile-right-panel, #wpadminbar.mobile' );
//            if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
//                $( 'body' ).removeClass( 'open-right open-left' );
//            }
//        } );

        /*------------------------------------------------------------------------------------------------------
         2.1 - Mobile/Tablet Carousels
         --------------------------------------------------------------------------------------------------------*/

        function mobile_carousel() {
            if ( is_mobile && has_item_nav ) {
                /* Remove submenu if there is any */
                if ( $( '#mobile-item-nav #nav-bar-filter .hideshow ul' ).length > 0 ) {
                    $( '#mobile-item-nav #nav-bar-filter' ).append( $( '#mobile-item-nav #nav-bar-filter .hideshow ul' ).html() );
                    $( '#mobile-item-nav #nav-bar-filter .hideshow' ).remove();
                }

                if ( !swiper ) {
                    // console.log( 'Setting up mobile nav swiper' );
                    swiper = $( '.mobile-item-nav-scroll-container' ).swiper( {
                        scrollContainer: true,
                        slideElement: 'div',
                        slideClass: 'mobile-item-nav',
                        wrapperClass: 'mobile-item-nav-wrapper'
                    } );
                }
            }
        }

        /*------------------------------------------------------------------------------------------------------
         2.2 - Responsive Dropdowns
         -------------------------------------------------------------------------------------------------------*/
        if ( typeof Selects !== 'undefined' ) {
            if ( $.isFunction( Selects.init_select ) ) {
                Selects.init_select( is_mobile, inputsEnabled );
            }
        }

        if ( $( 'body' ).hasClass( 'messages' ) ) {
            $document.ajaxComplete( function () {
                setTimeout( function () {
                    if ( $.isFunction( Selects.init_select ) ) {
                        Selects.init_select( is_mobile, inputsEnabled );
                    }
                    if ( typeof Selects !== 'undefined' ) {
                        if ( $.isFunction( Selects.populate_select_label ) ) {
                            Selects.populate_select_label( is_mobile );
                        }
                    }
//                    $('.message-check, #select-all-messages').addClass('styled').after('<strong></strong>');

                }, 500 );
            } );
        }

        $( window ).on( 'load', function () {
            $( 'body' ).addClass( 'boss-page-loaded' );
        } );

        $( '#mobile-right-panel .menu-item-has-children' ).each( function () {
            $( this ).prepend( '<i class="fa submenu-btn fa-angle-down"></i>' );
        } );

        $( '#mobile-right-panel .submenu-btn' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( this ).toggleClass( 'fa-angle-left fa-angle-down' ).parent().find( '.sub-menu' ).slideToggle();
        } );

        /*------------------------------------------------------------------------------------------------------
         2.3 - Notifications Area
         -------------------------------------------------------------------------------------------------------*/

        // Add Notifications Area, if there are notifications to show

        if ( is_mobile && $window.width() < 720 ) {

            if ( $( '#wp-admin-bar-bp-notifications' ).length != 0 ) {

                // Clone and Move the Notifications Count to the Header
                $( 'li#wp-admin-bar-bp-notifications a.ab-item > span#ab-pending-notifications' ).clone().appendTo( '#user-nav' );

            }
        }

        /*------------------------------------------------------------------------------------------------------
         3.0 - Send To Full Editor
         --------------------------------------------------------------------------------------------------------*/
        $( '#full-screen' ).on( 'submit', function ( e ) {
            var editor = new MediumEditor( '.sap-editable-area' ),
                title = new MediumEditor( '.sap-editable-title' ),
                post_content_wrap = $( '.sap-editable-area' ).attr( 'id' ),
                post_content_obj = editor.serialize(),
                post_title_obj = title.serialize(),
                post_title_wrap = $( '.sap-editable-title' ).attr( 'id' );
                        $( this ).find( 'input[name="content"]' ).val( post_content_obj[post_content_wrap].value.replace( /(\r\n|\n|\r)/gm, "" ) );
                        $( this ).find( 'input[name="title"]' ).val( post_title_obj[post_title_wrap].value.replace( /(\r\n|\n|\r)/gm, "" ) );
                } );

        /*------------------------------------------------------------------------------------------------------
         3.0 - Content
         --------------------------------------------------------------------------------------------------------*/

        if ( !$( '#buddyboss-media-add-photo' ).length ) {
            $( '#whats-new-textarea .boss-insert-buttons-addons li:first-child' ).remove();
        }

        $( '#whats-new-textarea .boss-insert-buttons-show' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( '#whats-new' ).removeAttr( 'placeholder' );
            $( this ).parents( '#whats-new-form' ).toggleClass( 'boss-show-buttons' );
        } );

        $( '#whats-new-textarea .boss-insert-image' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( '#buddyboss-media-open-uploader-button' ).trigger( 'click' );
        } );

        $( '#whats-new-textarea .boss-insert-video' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( '#whats-new-form' ).removeClass( 'boss-show-buttons' );
            $( '#whats-new' ).attr( 'placeholder', $( this ).data( 'placeholder' ) ).focus();
        } );

        $( '.comment-form-comment .boss-insert-buttons-show' ).on( 'click', function ( e ) {
            e.preventDefault();

            if ( $( this ).parents( '.comment-form' ).hasClass( 'boss-show-buttons' ) ) {
                setTimeout( function () {
                    $( '#comment' ).css( 'visibility', 'visible' ).attr( 'placeholder', $( '.comment-form-comment .boss-insert-text' ).data( 'placeholder' ) ).focus();
                }, 150 );
            } else {
                $( '#comment' ).removeAttr( 'placeholder' ).css( 'visibility', 'hidden' );
                ;
            }

            $( this ).parents( '.comment-form' ).toggleClass( 'boss-show-buttons' );
        } );

        $( '.comment-form-comment .boss-insert-image' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( '#attachment' ).trigger( 'click' );
        } );

        $( '#attachment' ).change( function () {
            String.prototype.filename = function ( extension ) {
                var s = this.replace( /\\/g, '/' );
                s = s.substring( s.lastIndexOf( '/' ) + 1 );
                // With extension
                return s;
                // Without extension
                // return extension ? s.replace( /[?#].+$/, '' ) : s.split( '.' )[0];
            };

            var fileName = $( this ).val().filename();
            $( '#comments' ).find( '.attached-image' ).remove();
            $( '#comment' ).after( '<p class="attached-image"><i class="fa fa-picture-o" aria-hidden="true"></i>' + fileName + '</p>' );
        } );

        // Turning On HTML5 Native Form Validation For WordPress Comments.
        $( '#commentform, #attachmentForm' ).removeAttr( 'novalidate' );

        $( '.comment-form-comment .boss-insert-text' ).on( 'click', function ( e ) {
            e.preventDefault();
            self = $( this );
            $( '#attachmentForm' ).removeClass( 'boss-show-buttons' );

            setTimeout( function () {
                $( '#comment' ).css( 'visibility', 'visible' ).attr( 'placeholder', self.data( 'placeholder' ) ).focus();
            }, 150 );
        } );

        $( 'body' ).on( 'mouseup', '.mfp-close', function () {
            $( '.RegisterBox' ).removeClass( 'go' );
            $( '.LoginBox' ).removeClass( 'go' );
        } );

        $( '#groups-dir-list' ).on( 'click', '.group-button a', function () {
            $( this ).parent().addClass( 'loading' );
        } );

        $( '#buddypress' ).on( 'click', '.group-button .leave-group', function () {
            $( this ).parent().addClass( 'loading' );
        } );

        $( document ).ajaxComplete( function () {
            $( '.generic-button.group-button' ).removeClass( 'loading' );
        } );

        $( 'body' ).on( 'change keyup keydown paste cut', '#comment', function () {
            $( this ).height( 0 ).height( this.scrollHeight );
        } ).find( 'textarea#comment' ).change();

        $( 'body' ).on( 'change keyup keydown paste cut focusout focus', '#whats-new', function () {
            $( this ).unbind( 'focus blur' );
            $( '#aw-whats-new-submit' ).prop( 'disabled', false );

            $( this ).height( 0 ).height( this.scrollHeight );

            var whats_content = $( this ).val();

            if ( whats_content !== '' ) {
                $( '.boss-insert-video' ).hide();
            } else {
                $( '.boss-insert-video' ).show();
            }
        } ).find( 'textarea#whats-new' ).change();

        /*--------------------------------------------------------------------------------------------------------
         3.21 - Responsive Menus (...)
         --------------------------------------------------------------------------------------------------------*/

        if ( !is_mobile ) {
            $( "body:not(.settings) #item-nav" ).find( "#nav-bar-filter" ).jRMenuMore( 60 );
            $( "#site-navigation .nav-menu" ).jRMenuMore( 120 );
        }

        /*------------------------------------------------------------------------------------------------------
         3.1 - Members (Group Admin)
         --------------------------------------------------------------------------------------------------------*/

        // Hide/Reveal action buttons
        $( 'a.show-options' ).click( function ( event ) {
            event.preventDefault();

            $( this ).next().slideToggle();

        } );


        /*------------------------------------------------------------------------------------------------------
         3.2 - Search Input Field
         --------------------------------------------------------------------------------------------------------*/
        $( '#buddypress div.dir-search form, #buddypress div.message-search form, div.bbp-search-form form, form#bbp-search-form' ).append( '<a href="#" id="clear-input"> </a>' );
        $( 'a#clear-input' ).click( function () {
            jQuery( "#buddypress div.dir-search form input[type=text], #buddypress div.message-search form input[type=text], div.bbp-search-form form input[type=text], form#bbp-search-form input[type=text]" ).val( "" );
        } );

        function searchWidthMobile() {
            if ( is_mobile ) {
                var $mobile_search = $( '#mobile-header .searchform' );
                if ( $mobile_search.length ) {
                    $mobile_search.focusin( function () {
                        $mobile_search.addClass( 'animate-form' );
                    } );

                    $mobile_search.focusout( function () {
                        $mobile_search.removeClass( 'animate-form' );
                    } );
                }
            }
        }

        searchWidthMobile();


        /*------------------------------------------------------------------------------------------------------
         3.3 - Hide Profile and Group Buttons Area, when there are no buttons (ex: Add Friend, Join Group etc...)
         --------------------------------------------------------------------------------------------------------*/

        if ( !$( '#buddypress #item-header #item-buttons .generic-button' ).length ) {
            $( '#buddypress #item-header #item-buttons' ).hide();
        }

        /*------------------------------------------------------------------------------------------------------
         3.4 - Load Posts on new submited
         --------------------------------------------------------------------------------------------------------*/

        $( document ).ajaxSuccess( function ( event, jqXHR, ajaxOptions, data ) {

            try {
                if ( jqXHR.status == 200 && ajaxOptions.data && ajaxOptions.data.indexOf( 'sap_save_post' ) > -1 && ajaxOptions.data.indexOf( 'post_status=public' ) > -1 ) {
                    $.ajax( {
                        url: BuddyBossOptions.ajaxurl,
                        type: 'post',
                        data: {
                            action: 'ajax_pagination',
                            id: data
                        },
                        success: function ( html ) {
                            $( '.boss-create-post-wrapper' ).after( html );
                        }
                    } );
                }
            } catch ( err ) {
                //console.log(err);
            }
        } );

        /*------------------------------------------------------------------------------------------------------
         3.5 - Spinners
         --------------------------------------------------------------------------------------------------------*/
        function initSpinner() {
            $( '.generic-button:not(.pending)' ).on( 'click', function () {
                $link = $( this ).find( 'a' );
                if ( !$link.find( 'i' ).length && !$link.hasClass( 'pending' ) ) {
                    $link.append( '<i class="fa fa-spinner fa-spin"></i>' );
                }
            } );
        }

        initSpinner();

        $( document ).ajaxComplete( function () {
            setTimeout( function () {
                initSpinner();
            }, 500 );

            setTimeout( function () {
                initSpinner();
            }, 1500 );
        } );
        /*------------------------------------------------------------------------------------------------------
         3.5 - Select unread and read messages in inbox
         --------------------------------------------------------------------------------------------------------*/

        // Overwrite/Re-do some of the functionality in buddypress.js,
        // to accommodate for UL instead of tables in buddyboss theme
        jq( "#message-type-select" ).change(
            function () {
                var selection = jq( "#message-type-select" ).val();
                var checkboxes = jq( "ul input[type='checkbox']" );
                checkboxes.each( function ( i ) {
                    checkboxes[i].checked = "";
                } );

                switch ( selection ) {
                    case 'unread':
                        var checkboxes = jq( "ul.unread input[type='checkbox']" );
                        break;
                    case 'read':
                        var checkboxes = jq( "ul.read input[type='checkbox']" );
                        break;
                }
                if ( selection != '' ) {
                    checkboxes.each( function ( i ) {
                        checkboxes[i].checked = "checked";
                    } );
                } else {
                    checkboxes.each( function ( i ) {
                        checkboxes[i].checked = "";
                    } );
                }
            }
        );

        /* Bulk delete messages */
        jq( "#delete_inbox_messages, #delete_sentbox_messages" ).on( 'click', function () {
            checkboxes_tosend = '';
            checkboxes = jq( "#message-threads ul input[type='checkbox']" );

            jq( '#message' ).remove();
            jq( this ).addClass( 'loading' );

            jq( checkboxes ).each( function ( i ) {
                if ( jq( this ).is( ':checked' ) )
                    checkboxes_tosend += jq( this ).attr( 'value' ) + ',';
            } );

            if ( '' == checkboxes_tosend ) {
                jq( this ).removeClass( 'loading' );
                return false;
            }

            jq.post( ajaxurl, {
                action: 'messages_delete',
                'thread_ids': checkboxes_tosend
            }, function ( response ) {
                if ( response[0] + response[1] == "-1" ) {
                    jq( '#message-threads' ).prepend( response.substr( 2, response.length ) );
                } else {
                    jq( '#message-threads' ).before( '<div id="message" class="updated"><p>' + response + '</p></div>' );

                    jq( checkboxes ).each( function ( i ) {
                        if ( jq( this ).is( ':checked' ) )
                            jq( this ).parent().parent().fadeOut( 150 );
                    } );
                }

                jq( '#message' ).hide().slideDown( 150 );
                jq( "#delete_inbox_messages, #delete_sentbox_messages" ).removeClass( 'loading' );
            } );
            return false;
        } );

        /*------------------------------------------------------------------------------------------------------
         3.6 - Make Video Embeds Responsive - Fitvids.js
         --------------------------------------------------------------------------------------------------------*/

        if ( typeof $.fn.fitVids !== 'undefined' && $.isFunction( $.fn.fitVids ) ) {
            $( '.wp-video' ).find( 'object' ).addClass( 'fitvidsignore' );
            $( '#content' ).fitVids();

            // This ensures that after and Ajax call we check again for
            // videos to resize.
            var fitVidsAjaxSuccess = function () {
                $( '.wp-video' ).find( 'object' ).addClass( 'fitvidsignore' );
                $( '#content' ).fitVids();
            }
            $( document ).ajaxSuccess( fitVidsAjaxSuccess );
        }

        /*--------------------------------------------------------------------------------------------------------
         3.7 - Infinite Scroll
         --------------------------------------------------------------------------------------------------------*/

        if ( $( '#masthead' ).data( 'infinite' ) == 'on' ) {
            var is_activity_loading = false;//We'll use this variable to make sure we don't send the request again and again.

            jq( document ).on( 'scroll', function () {
                //Find the visible "load more" button.
                //since BP does not remove the "load more" button, we need to find the last one that is visible.
                var load_more_btn = jq( ".load-more:visible" );
                //If there is no visible "load more" button, we've reached the last page of the activity stream.
                if ( !load_more_btn.get( 0 ) )
                    return;

                //Find the offset of the button.
                var pos = load_more_btn.offset();

                //If the window height+scrollTop is greater than the top offset of the "load more" button, we have scrolled to the button's position. Let us load more activity.
                //            console.log(jq(window).scrollTop() + '  '+ jq(window).height() + ' '+ pos.top);

                if ( jq( window ).scrollTop() + jq( window ).height() > pos.top ) {

                    load_more_activity();
                }

            } );

            /**
             * This routine loads more activity.
             * We call it whenever we reach the bottom of the activity listing.
             *
             */
            function load_more_activity() {

                //Check if activity is loading, which means another request is already doing this.
                //If yes, just return and let the other request handle it.
                if ( is_activity_loading )
                    return false;

                //So, it is a new request, let us set the var to true.
                is_activity_loading = true;

                //Add loading class to "load more" button.
                //Theme authors may need to change the selector if their theme uses a different id for the content container.
                //This is designed to work with the structure of bp-default/derivative themes.
                //Change #content to whatever you have named the content container in your theme.
                jq( "#content li.load-more" ).addClass( 'loading' );


                if ( null == jq.cookie( 'bp-activity-oldestpage' ) )
                    jq.cookie( 'bp-activity-oldestpage', 1, {
                        path: '/'
                    } );

                var oldest_page = ( jq.cookie( 'bp-activity-oldestpage' ) * 1 ) + 1;

                //Send the ajax request.
                jq.post( ajaxurl, {
                    action: 'activity_get_older_updates',
                    'cookie': encodeURIComponent( document.cookie ),
                    'page': oldest_page
                },
                function ( response )
                {
                    jq( ".load-more" ).hide();//Hide any "load more" button.
                    jq( "#content li.load-more" ).removeClass( 'loading' );//Theme authors, you may need to change #content to the id of your container here, too.

                    //Update cookie...
                    jq.cookie( 'bp-activity-oldestpage', oldest_page, {
                        path: '/'
                    } );

                    //and append the response.
                    jq( "#content ul.activity-list" ).append( response.contents );

                    //Since the request is complete, let us reset is_activity_loading to false, so we'll be ready to run the routine again.

                    is_activity_loading = false;
                }, 'json' );

                return false;

            }
        }

        /*--------------------------------------------------------------------------------------------------------
         3.8 - Switch Layout
         --------------------------------------------------------------------------------------------------------*/
        if ( is_mobile ) {
            $( '#switch_mode' ).val( 'desktop' );
            $( '#switch_submit' ).val( translation.view_desktop );
        } else {
            $( '#switch_mode' ).val( 'mobile' );
            $( '#switch_submit' ).val( translation.view_mobile );
        }

        $( '#switch_submit' ).click( function () {
            $.cookie( 'switch_mode', $( '#switch_mode' ).val(), { path: '/' } );
        } );

        /*--------------------------------------------------------------------------------------------------------
         3.9 - Search
         --------------------------------------------------------------------------------------------------------*/

        var $search_form = $( '#header-search' ).find( 'form' );

        $( '#search-open' ).click( function ( e ) {
            e.preventDefault();
            $search_form.fadeIn();
            setTimeout( function () {
                $search_form.find( '#s' ).focus();
            }, 301 );
        } );

        $( '#search-close' ).click( function ( e ) {
            e.preventDefault();
            $search_form.fadeOut();
        } );

        $( document ).click( function ( e )
        {
            var container = $( "#header-search" );

            if ( !container.is( e.target ) // if the target of the click isn't the container...
                && container.has( e.target ).length === 0 ) // ... nor a descendant of the container
            {
                $search_form.fadeOut();
            }
        } );

        function search_width() {
            var buttons_width = 0;

            $( '#header-search' ).nextAll( 'div, a' ).each( function () {
                buttons_width = buttons_width + $( this ).width();
            } );

            $search_form.width( $( '.header-wrapper' ).width() - 180 - buttons_width );
        }

        search_width();
        $window.resize( function () {
//            setTimeout(function(){
            search_width();
//            }, 10);
        } );

        //BuddyBoss Inbox Additional Label Functionality Code

        jQuery( document ).on( 'click', '.bb-add-label-button', function ( e ) {
            e.preventDefault();

            _this = jQuery( this );

            _this.find( ".fa-spin" ).fadeOut();

            var label_name = jQuery( '.bb-label-name' ).val();
            var data = {
                action: 'bbm_label_ajax',
                task: 'add_new_label',
                thread_id: 0,
                label_name: label_name
            };

            jQuery.post( ajaxurl, data, function ( response ) {

                _this.find( ".fa-spin" ).fadeIn();

                var response = jQuery.parseJSON( response );

                if ( response.label_id != '' ) {

                    jQuery( ".bb-label-container" ).load( window.location.href + " .bb-label-container", function () {
                        jQuery( ".bb-label-container > .bb-label-container" ).attr( "class", "" );
                    } );

                }

                if ( typeof response.message == 'undefined' ) {
                    return false;
                }

                if ( response.message != '' ) {
                    alert( response.message );
                }

            } );
        } );

        jQuery( document ).on( "keydown", ".bb-label-name", function ( e ) {
            if ( e.keyCode == 13 ) {
                jQuery( ".bb-add-label-button" ).click();
            }
        } );

        /*--------------------------------------------------------------------------------------------------------
         3.9 - Sidebar
         --------------------------------------------------------------------------------------------------------*/
        if ( !is_mobile ) {
            $( '#trigger-sidebar' ).click( function ( e ) {
                e.preventDefault();
                $( 'body' ).toggleClass( 'bb-sidebar-on' );
            } );
        }

        /*--------------------------------------------------------------------------------------------------------
         3.14 - To Top Button
         --------------------------------------------------------------------------------------------------------*/
        //Scroll Effect
        $( '.to-top' ).bind( 'click', function ( event ) {

            event.preventDefault();

            var $anchor = $( this );

            //, 'easeInOutExpo'
            $( 'html, body' ).stop().animate( {
                scrollTop: "0px"
            }, 500 );

        } );

        // Sticky Header
        var topSpace = 0;
        if ( $( '#wpadminbar' ).is( ':visible' ) ) {
            topSpace = 32;
        }

        $( '.sticky-header #masthead' ).sticky( { topSpacing: topSpace } );

        /*--------------------------------------------------------------------------------------------------------
         3.15 - Friends Lists
         --------------------------------------------------------------------------------------------------------*/
        $document.ajaxComplete( function () {
            $( 'ul.horiz-gallery h5, ul#group-admins h5, ul#group-mods h5' ).each( function () {
                $( this ).css( {
                    left: -$( this ).width() / 2 + 25
                } );
            } );
        } );

        $( '.trigger-filter' ).click( function () {
            $( this ).next().fadeToggle( 200 );
            $( this ).toggleClass( 'notactive active' );
        } );

        /*--------------------------------------------------------------------------------------------------------
         3.16 - Profile Settings Radio Buttons
         --------------------------------------------------------------------------------------------------------*/
        $( '#buddypress table.notification-settings .yes input[type="radio"]' ).after( '<label>'+translation.yes+'</label>' );
        $( '#buddypress table.notification-settings .no input[type="radio"]' ).after( '<label>'+translation.no+'</label>' );

        /*--------------------------------------------------------------------------------------------------------
         3.17 - 404 carousel posts
         --------------------------------------------------------------------------------------------------------*/

        function initFullwidthCarousel() {
            $( '#posts-carousel ul' ).carouFredSel( {
                responsive: true,
                width: '100%',
                items: {
                    width: 310,
                    visible: {
                        min: 1,
                        max: 1
                    }
                },
                swipe: {
                    onTouch: true,
                    onMouse: true
                },
                scroll: {
                    items: 1,
                    fx: 'crossfade',
                    onBefore: $carouselHighlight,
                },
                auto: false,
                prev: {
                    button: function () {
                        return $( this ).closest( '#posts-carousel' ).find( '#prev' );
                    },
                    key: "left"
                },
                next: {
                    button: function () {
                        return $( this ).closest( '#posts-carousel' ).find( '#next' );
                    },
                    key: "right"
                }
            } );

        }

        //carouFredSel added "loaded" css class to the active slide
        var $carouselHighlight = function() {
            var $this = $("#posts-carousel ul");
            var items = $this.triggerHandler("currentVisible");     //get all visible items
            $this.children().removeClass("loaded");                 // remove all .active classes
            items.filter(":eq(1)").addClass("loaded");              // add .active class to n-th item
        };

        if ( $( '#posts-carousel' ).length > 0 ) {
            initFullwidthCarousel();
        }

        /*--------------------------------------------------------------------------------------------------------
         3.18 - Testimonials
         --------------------------------------------------------------------------------------------------------*/

        //Set testimonial item active on mousehover( hoverIntent ) :)
        function setActiveTestimonials() {
            $( ".testimonials-wrap" ).each( function () {
                var t = $( this ),
                    n = t.find( ".author-images" ),
                    r = t.find( ".testimonial-items" );
                n.find( "span" ).hoverIntent( function () {
                    var t = $( this ).attr( "data-id" ),
                        n = r.find( 'div[id^="' + t + '"]' ),
                        i = n.outerHeight() + 30;
                    $( this ).parent().siblings( "li" ).removeClass( "active-test-item" ).end().addClass( "active-test-item" );
                    r.css( "height", i );
                    r.find( ".testimonial" ).removeClass( "active-test" );
                    n.addClass( "active-test" );
                } );
            } );
        }

        $( window ).resize( setActiveTestimonials );

        //Init testimonial slider and set the first item as an active
        function initTestimonials() {
            $( ".testimonials-wrap" ).each( function () {
                var t = $( this ),
                    n = t.find( ".author-images" ),
                    r = t.find( ".testimonial-items" );

                //Set first item as an active
                n.find( "li" ).first().addClass( "active-test-item" );
                r.css( "height", r.find( ".testimonial" ).first().outerHeight() + 30 );
                r.find( ".testimonial" ).first().addClass( "active-test" );
            } );
        }

        initTestimonials();

        /*--------------------------------------------------------------------------------------------------------
         3.18 - BuddyPanel bubbles
         --------------------------------------------------------------------------------------------------------*/

        function setCounters() {
            $( '#wp-admin-bar-my-account-buddypress' ).find( 'li' ).each( function () {
                var $this = $( this ),
                    $count = $this.children( 'a' ).children( '.count' ),
                    id,
                    $target;

                if ( $count.length != 0 ) {
                    id = $this.attr( 'id' );
                    $target = $( '.bp-menu.bp-' + id.replace( /wp-admin-bar-my-account-/, '' ) + '-nav' );
                    if ( $target.find( '.count' ).length == 0 ) {
                        $target.find( 'a' ).append( '<span class="count">' + $count.html() + '</span>' );
                    }
                }
            } );
        }

        setCounters();

        /*--------------------------------------------------------------------------------------------------------
         3.20 - Post activity
         --------------------------------------------------------------------------------------------------------*/
        var $whats_form = $( '#whats-new-form' );

        $( '#whats-new-header-content' ).click( function () {
            $whats_form.toggleClass();
            if ( !$whats_form.hasClass( 'isCollapsed' ) ) {
                var content = $( '#whats-new' ).val();
                content = content.trim();

                $( '#whats-new' ).val( content ).focus();
            }
        } );

        if ( $( '#whats-new' ).size() > 0 ) {
            var whats_content = $( '#whats-new' ).val();
            whats_content = whats_content.trim();

            if ( whats_content !== '' ) {
                setTimeout( function () {
                    $( '#whats-new-header-content' ).trigger( 'click' );
                }, 600 );
            }
        }

        $( '#aw-whats-new-submit' ).on( 'click', function () {
            $( '#whats-new' ).attr( 'placeholder', '' );
            $( '.boss-insert-video' ).show();
        } );

        $( '#whats-new-close' ).click( function () {
            $whats_form.addClass( 'isCollapsed' );
            $( '#whats-new-form' ).removeClass( 'boss-show-buttons' );
        } );

        $( '.boss-author-info' ).on( 'click', function () {
            $( this ).parents( '.boss-create-post-wrapper' ).toggleClass( 'isCollapsed' );
        } );

        $( '.boss-close-create-post' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( '.boss-create-post-wrapper' ).removeClass( 'isCollapsed' );
        } );

        $( document ).keyup( function ( e ) {
            if ( e.keyCode === 27 ) {
                // escape key maps to keycode `27`
                if ( ( '.boss-close-create-post' ).length ) {
                    $( '.boss-close-create-post' ).trigger( 'click' );
                }

                $whats_form.addClass( 'isCollapsed' );
                $( '#whats-new-form' ).removeClass( 'boss-show-buttons' );
            }
        } );

        $( document ).on( 'mouseup', '.boss-create-post-wrapper', function ( e ) {
            var container = $( ".boss-create-post-wrapper" );

            /* if the target of the click isn't the container */
            /* Nor a descendant of the container */

            if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
                container.removeClass( 'isCollapsed' );
            }
        } );

        /*------------------------------------------------------------------------------------------------------
         3.21 - Post activity buttons and selects
         --------------------------------------------------------------------------------------------------------*/

        $( '#buddyboss-media-add-photo' ).insertAfter( '#whats-new-close' );
        $( '#whats-new-selects' ).prepend( $( '#activity-visibility' ) );

        /*--------------------------------------------------------------------------------------------------------
         3.22 - Group Join button clip text
         --------------------------------------------------------------------------------------------------------*/

        $( '.inner-avatar-wrap' ).find( 'a.group-button' ).each( function () {
            var oldText = $( this ).text();
            $( this ).text( oldText.substring( 0, oldText.indexOf( " " ) ) );
        } );

        /*--------------------------------------------------------------------------------------------------------
         3.25 - Better Radios and Checkboxes Styling
         --------------------------------------------------------------------------------------------------------*/
        function initCheckboxes() {
            if ( !inputsEnabled ) {
                //only few buddypress and bbpress related fields
                $( '#frm_buddyboss-media-tag-friends input[type="checkbox"], #buddypress table.notifications input, #send_message_form input[type="checkbox"], #profile-edit-form input[type="checkbox"],  #profile-edit-form input[type="radio"], #message-threads input, #settings-form input[type="radio"], #create-group-form input[type="radio"], #create-group-form input[type="checkbox"], #invite-list input[type="checkbox"], #group-settings-form input[type="radio"], #group-settings-form input[type="checkbox"], #new-post input[type="checkbox"], .bbp-form input[type="checkbox"], .bbp-form .input[type="radio"], .register-section .input[type="radio"], .register-section input[type="checkbox"], .message-check, #select-all-messages, #siteLoginBox input[type="checkbox"], #siteRegisterBox input[type="checkbox"]' ).each( function () {
                    var $this = $( this );
                    $this.addClass( 'styled' );
                    if ( $this.next( "label" ).length == 0 && $this.next( "strong" ).length == 0 ) {
                        $this.after( '<strong></strong>' );
                    }
                } );
            } else {
                //all fields
                $( 'input[type="checkbox"], input[type="radio"]' ).each( function () {
                    var $this = $( this );
                    if ( $this.val() == 'gf_other_choice' ) {
                        $this.addClass( 'styled' );
                        $this.next().wrap( '<strong class="other-option"></strong>' );
                    } else {
                        if ( !$this.parents( '#bp-group-documents-form' ).length ) {
                            $this.addClass( 'styled' );
                            if ( $this.next( "label" ).length == 0 && $this.next( "strong" ).length == 0 ) {
                                $this.after( '<strong></strong>' );
                            }
                        }
                    }
                } );
            }
        }

        initCheckboxes();

        $( document ).ajaxSuccess( function () {
            initCheckboxes();
        });

//        $('#buddypress table.notification-settings td input').after('<label></label>');
        $( '#buddypress table.notifications input' ).after( '<strong></strong>' );

        if ( $( '#groupblog-member-options' ).length ) {
            $( '#groupblog-member-options > input[type="radio"]' ).each( function () {
                $( this ).next( 'span' ).andSelf().wrapAll( '<div class="member-options-wrap"/>' );
            } );
        }

        $( ".header-notifications .pending-count" ).html( '<b>' + jQuery( ".header-notifications .pending-count" ).html() + '</b>' );

        /*--------------------------------------------------------------------------------------------------------
         3.27 - Account Menu PopUp On Touch For Tablets
         --------------------------------------------------------------------------------------------------------*/
        $( '.tablet .header-account-login' ).on( "click touch", function ( e ) {
            $( this ).find( '.pop' ).toggleClass( 'hover' );
        } );

        $( '.tablet .header-account-login > a' ).on( "click touch", function ( e ) {
            e.preventDefault();
        } );
        /*--------------------------------------------------------------------------------------------------------
         3.28 - BuddyPress Group Email Subscription
         --------------------------------------------------------------------------------------------------------*/

        $( '#item-meta .group-subscription-options-link' ).on("click", function() {
            stheid = $(this).attr('id').split('-');
            group_id = stheid[1];
            $( '.group-subscription-options' ).slideToggle();
        });

        $( '#item-meta .group-subscription-close' ).on("click", function() {
            stheid = $(this).attr('id').split('-');
            group_id = stheid[1];
            $( '.group-subscription-options' ).slideToggle();
        });

        /*--------------------------------------------------------------------------------------------------------
         3.29 - Cart Dropdown
         --------------------------------------------------------------------------------------------------------*/

        $(document).on('change','#calc_shipping_country', function(){
            var $field = $('#calc_shipping_state_field');
            if($field.find('input').length > 0){
                $field.addClass('plain-input');
            } else {
                $field.removeClass('plain-input');
            }
        });

        /*------------------------------------------------------------------------------------------------------
         Heartbeat functions
         --------------------------------------------------------------------------------------------------------*/

        //Notifications related updates
        $( document ).on( 'heartbeat-tick.bb_notification_count', function ( event, data ) {
            setCounters();

            if ( data.hasOwnProperty( 'bb_notification_count' ) ) {
                data = data['bb_notification_count'];
                /********notification type**********/
                if ( data.notification > 0 ) { //has count
                    jQuery( "#ab-pending-notifications" ).html( '<b>' + data.notification + '</b>' ).removeClass( "no-alert" );
                    jQuery( "#ab-pending-notifications-mobile" ).html( '<b>' + data.notification + '</b>' ).removeClass( "no-alert" );
                    jQuery( ".ab-item[href*='/notifications/']" ).each( function () {
                        jQuery( this ).append( "<span class='count'><b>" + data.notification + "</b></span>" );
                        if ( jQuery( this ).find( ".count" ).length > 1 ) {
                            jQuery( this ).find( ".count" ).first().remove(); //remove the old one.
                        }
                    } );
                } else {
                    jQuery( "#ab-pending-notifications" ).html( '<b>' + data.notification + '</b>' ).addClass( "no-alert" );
                    jQuery( "#ab-pending-notifications-mobile" ).html( '<b>' + data.notification + '</b>' ).addClass( "no-alert" );
                    jQuery( ".ab-item[href*='/notifications/']" ).each( function () {
                        jQuery( this ).find( ".count" ).remove();
                    } );
                }
                //remove from read ..
                jQuery( ".mobile #wp-admin-bar-my-account-notifications-read, #adminbar-links #wp-admin-bar-my-account-notifications-read" ).each( function () {
                    $( this ).find( "a" ).find( ".count" ).remove();
                } );
                /**********messages type************/
                if ( data.unread_message > 0 ) { //has count
                    jQuery( "#user-messages" ).find( "span" ).html( '<b>' + data.unread_message + '</b>' ).removeClass( "no-alert" );
                    jQuery( ".ab-item[href*='/messages/']" ).each( function () {
                        jQuery( this ).append( "<span class='count'><b>" + data.unread_message + "</b></span>" );
                        if ( jQuery( this ).find( ".count" ).length > 1 ) {
                            jQuery( this ).find( ".count" ).first().remove(); //remove the old one.
                        }
                    } );
                } else {
                    jQuery( "#user-messages" ).find( "span" ).html( '<b>' + data.unread_message + '</b>' ).addClass( "no-alert" );
                    jQuery( ".ab-item[href*='/messages/']" ).each( function () {
                        jQuery( this ).find( ".count" ).remove();
                    } );
                }
                //remove from unwanted place ..
                jQuery( ".mobile #wp-admin-bar-my-account-messages-default, #adminbar-links #wp-admin-bar-my-account-messages-default" ).find( "li:not('#wp-admin-bar-my-account-messages-inbox')" ).each( function () {
                    jQuery( this ).find( "span" ).remove();
                } );
                /**********messages type************/
                if ( data.friend_request > 0 ) { //has count
                    jQuery( ".ab-item[href*='/friends/']" ).each( function () {
                        jQuery( this ).append( "<span class='count'><b>" + data.friend_request + "</b></span>" );
                        if ( jQuery( this ).find( ".count" ).length > 1 ) {
                            jQuery( this ).find( ".count" ).first().remove(); //remove the old one.
                        }
                    } );
                } else {
                    jQuery( ".ab-item[href*='/friends/']" ).each( function () {
                        jQuery( this ).find( ".count" ).remove();
                    } );
                }
                //remove from unwanted place ..
                jQuery( ".mobile #wp-admin-bar-my-account-friends-default, #adminbar-links #wp-admin-bar-my-account-friends-default" ).find( "li:not('#wp-admin-bar-my-account-friends-requests')" ).each( function () {
                    jQuery( this ).find( "span" ).remove();
                } );

                //notification content
                jQuery( "#all-notificatios .pop ul" ).html( data.notification_content );
            }
        } );


    }

    /*================================================================================================
     ** Cover Photo Functions **
     ==================================================================================================*/

    buddyboss_cover_photo = function ( option ) {

        $bb_cover_photo = $( "#page .bb-cover-photo:last" );
        object = $bb_cover_photo.data( "obj" ); // user or group
        object_id = $bb_cover_photo.data( "objid" ); // id of user or group
        nonce = $bb_cover_photo.data( "nonce" );
        $refresh_button = $( "#refresh-cover-photo-btn" );

        rebind_refresh_cover_events = function () {
            $refresh_button.click( function () {
                $( '.bb-cover-photo #growls' ).remove();
                $( "#update-cover-photo-btn" ).prop( "disabled", true ).removeClass( 'uploaded' ).addClass( 'disabled' ).find( "i" ).fadeIn();
                $refresh_button.prop( "disabled", true ).removeClass( 'uploaded' ).addClass( 'disabled' ).find( "i" ).fadeIn();

                $.ajax( {
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        'action': 'buddyboss_cover_photo_refresh',
                        'object': object,
                        'object_id': object_id,
                        'nonce': option.nonce,
                        'routine': $refresh_button.data( 'routine' )
                    },
                    success: function ( response ) {
                        var responseJSON = $.parseJSON( response );

                        $( "#update-cover-photo-btn" ).prop( "disabled", false ).removeClass( 'disabled' ).addClass( 'uploaded' ).find( "i.fa-spin" ).fadeOut();
                        $refresh_button.prop( "disabled", false ).removeClass( 'disabled' ).addClass( 'uploaded' ).find( "i.fa-spin" ).fadeOut();

                        if ( !responseJSON ) {
                            $.growl.error( { title: "", message: BuddyBossOptions.bb_cover_photo_failed_refresh } );
                        }

                        if ( responseJSON.error ) {
                            $.growl.error( { title: "", message: responseJSON.error } );
                        } else {
                            $bb_cover_photo.find( ".holder" ).remove();

                            image = responseJSON.image;
                            $bb_cover_photo.append( '<div class="holder"></div>' );
                            $bb_cover_photo.find( ".holder" ).css( "background-image", 'url(' + image + ')' );

                            if ( 'refresh' == $refresh_button.data( 'routine' ) ) {
                                $refresh_button.parent().toggleClass( 'no-photo' );
                                $refresh_button.find( '.fa-refresh' ).removeClass( 'fa-refresh' ).addClass( 'fa-times' );
                                $refresh_button.find( '>div' ).html( BuddyBossOptions.bb_cover_photo_remove_title + '<i class="fa fa-spinner fa-spin" style="display: none;"></i>' );
                                $refresh_button.attr( 'title', BuddyBossOptions.bb_cover_photo_remove_title );
                                $refresh_button.data( 'routine', 'remove' );
                            } else {
                                $refresh_button.parent().toggleClass( 'no-photo' );
                                $refresh_button.find( '.fa-times' ).removeClass( 'fa-times' ).addClass( 'fa-refresh' );
                                $refresh_button.find( '>div' ).html( BuddyBossOptions.bb_cover_photo_refresh_title + '<i class="fa fa-spinner fa-spin" style="display: none;"></i>' );
                                $refresh_button.attr( 'title', BuddyBossOptions.bb_cover_photo_refresh_title );
                                $refresh_button.data( 'routine', 'refresh' );
                            }
                            $.growl.notice( { title: "", message: responseJSON.success } );
                        }
                    },
                    error: function ( ) {
                        $bb_cover_photo.find( ".progress" ).hide().find( "span" ).css( "width", '0%' );

                        $.growl.error( { message: 'Error' } );
                    }
                } );
            } );
        };

        if ( $refresh_button.length > 0 ) {
            rebind_refresh_cover_events();
        }
    }

    /** --------------------------------------------------------------- */

    /**
     * BuddyPress Legacy Support
     */

    // Initialize
    BP_Legacy.init = function () {
        BP_Legacy.injected = false;
        _l.$document.ready( BP_Legacy.domReady );
    }

    // On dom ready we'll check if we need legacy BP support
    BP_Legacy.domReady = function () {
        BP_Legacy.check();
    }

    // Check for legacy support
    BP_Legacy.check = function () {
        if ( !BP_Legacy.injected && _l.body.hasClass( 'buddypress' ) && _l.$buddypress.length == 0 ) {
            BP_Legacy.inject();
        }
    }

    // Inject the right code depending on what kind of legacy support
    // we deduce we need
    BP_Legacy.inject = function () {
        BP_Legacy.injected = true;

        var $secondary = $( '#secondary' ),
            do_legacy = false;

        var $content = $( '#content' ),
            $padder = $content.find( '.padder' ).first(),
            do_legacy = false;

        var $article = $content.children( 'article' ).first();

        var $legacy_page_title,
            $legacy_item_header;

        // Check if we're using the #secondary widget area and add .bp-legacy inside that
        if ( $secondary.length ) {
            $secondary.prop( 'id', 'secondary' ).addClass( 'bp-legacy' );

            do_legacy = true;
        }

        // Check if the plugin is using the #content wrapper and add #buddypress inside that
        if ( $padder.length ) {
            $padder.prop( 'id', 'buddypress' ).addClass( 'bp-legacy entry-content' );

            do_legacy = true;

            // console.log( 'Buddypress.js #buddypress fix: Adding #buddypress to .padder' );
        }
        else if ( $content.length ) {
            //$content.wrapInner( '<div class="bp-legacy entry-content" id="buddypress"/>' );

            do_legacy = true;

            // console.log( 'Buddypress.js #buddypress fix: Dynamically wrapping with #buddypresss' );
        }

        // Apply legacy styles if needed
        if ( do_legacy ) {

            _l.$buddypress = $( '#buddypress' );

            $legacy_page_title = $( '.buddyboss-bp-legacy.page-title' );
            $legacy_item_header = $( '.buddyboss-bp-legacy.item-header' );

            // Article Element
            if ( $article.length === 0 ) {
                $content.wrapInner( '<article/>' );
                $article = $( $content.find( 'article' ).first() );
            }

            // Page Title
            if ( $content.find( '.entry-header' ).length === 0 || $content.find( '.entry-title' ).length === 0 ) {
                $legacy_page_title.prependTo( $article ).show();
                $legacy_page_title.children().unwrap();
            }

            // Item Header
            if ( $content.find( '#item-header-avatar' ).length === 0 && _l.$buddypress.find( '#item-header' ).length ) {
                $legacy_item_header.prependTo( _l.$buddypress.find( '#item-header' ) ).show();
                $legacy_item_header.children().unwrap();
            }
        }
    }

    // Boot er' up
    jQuery( document ).ready( function () {
        App.init();
    } );

}( jQuery, window ) );


/**
 * 3. Inline Plugins
 * ====================================================================
 * Inline Plugins
 */


/**
 * jRMenuMore to allow menu to have a More option for responsiveness
 * Credit to http://blog.sodhanalibrary.com/2014/02/jrmenumore-jquery-plugin-for-responsive.html
 *
 * uses resize.js for better resizing
 *
 **/
( function ( $ ) {
    $.fn.jRMenuMore = function ( widthfix ) {
        $( this ).each( function () {
            $( this ).addClass( "horizontal-responsive-menu" );
            alignMenu( this );
            var robj = this;

            $( '#main-wrap' ).resize( function () {
                $( robj ).append( $( $( $( robj ).children( "li.hideshow" ) ).children( "ul" ) ).html() );
                $( robj ).children( "li.hideshow" ).remove();
                alignMenu( robj );
            } );

            function alignMenu( obj ) {
                var w = 0;
                var mw = $( obj ).width() - widthfix;
                var i = -1;
                var menuhtml = '';
                jQuery.each( $( obj ).children(), function () {
                    i++;
                    w += $( this ).outerWidth( true );
                    if ( mw < w ) {
                        menuhtml += $( '<div>' ).append( $( this ).clone() ).html();
                        $( this ).remove();
                    }
                } );

                $( obj ).append(
                    '<li class="hideshow">' +
                    '<a class="bb-menu-button" href="#"><i class="fa"></i></a><ul>' +
                    menuhtml + '</ul></li>' );
                $( obj ).children( "li.hideshow ul" ).css( "top",
                    $( obj ).children( "li.hideshow" ).outerHeight( true ) + "px" );

                $( obj ).find( "li.hideshow > a" ).click( function ( e ) {
                    e.preventDefault();
                    $( this ).parent( 'li.hideshow' ).children( "ul" ).toggle();
                    $( this ).parent( 'li.hideshow' ).parent( "ul" ).toggleClass( 'open' );
                } );

                $( document ).mouseup( function ( e ) {
                    var container = $( 'li.hideshow' );

                    if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
                        container.children( "ul" ).hide();
                        container.parent( "ul" ).removeClass( 'open' );
                    }
                } );

                if ( $( obj ).find( "li.hideshow" ).find( "li" ).length > 0 ) {
                    $( obj ).find( "li.hideshow" ).show();
                } else {
                    $( obj ).find( "li.hideshow" ).hide();
                }
            }

        } );

    }

}( jQuery ) );

/*------------------------------------------------------------------------------------------------------
 Inline Plugins
 --------------------------------------------------------------------------------------------------------*/

/*
 * jQuery Mobile Plugin: jQuery.Event.Special.Fastclick
 * http://nischenspringer.de/jquery/fastclick
 *
 * Copyright 2013 Tobias Plaputta
 * Released under the MIT license.
 * http://nischenspringer.de/license
 *
 */
;
( function ( e ) {
    var t = e( [ ] ), n = 800, r = 30, i = 10, s = [ ], o = { };
    var u = function ( e ) {
        var t, n;
        for ( t = 0, n = s.length; t < n; t++ ) {
            if ( Math.abs( e.pageX - s[t].x ) < r && Math.abs( e.pageY - s[t].y ) < r ) {
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault()
            }
        }
    };
    var a = true;
    if ( Modernizr && Modernizr.hasOwnProperty( "touch" ) ) {
        a = Modernizr.touch
    }
    var f = function () {
        s.splice( 0, 1 )
    };
    e.event.special.fastclick = { touchstart: function ( t ) {
            o.startX = t.originalEvent.touches[0].pageX;
            o.startY = t.originalEvent.touches[0].pageY;
            o.hasMoved = false;
            e( this ).on( "touchmove", e.event.special.fastclick.touchmove )
        }, touchmove: function ( t ) {
            if ( Math.abs( t.originalEvent.touches[0].pageX - o.startX ) > i || Math.abs( t.originalEvent.touches[0].pageX - o.startY ) > i ) {
                o.hasMoved = true;
                e( this ).off( "touchmove", e.event.special.fastclick.touchmove )
            }
        }, add: function ( t ) {
            if ( !a ) {
                return
            }
            var r = e( this );
            r.data( "objHandlers" )[t.guid] = t;
            var i = t.handler;
            t.handler = function ( t ) {
                r.off( "touchmove", e.event.special.fastclick.touchmove );
                if ( !o.hasMoved ) {
                    s.push( { x: o.startX, y: o.startY } );
                    window.setTimeout( f, n );
                    var u = this;
                    var a = e( [ ] );
                    var l = arguments;
                    e.each( r.data( "objHandlers" ), function () {
                        if ( !this.selector ) {
                            if ( r[0] == t.target || r.has( t.target ).length > 0 )
                                i.apply( r, l )
                        } else {
                            e( this.selector, r ).each( function () {
                                if ( this == t.target || e( this ).has( t.target ).length > 0 )
                                    i.apply( this, l )
                            } )
                        }
                    } )
                }
            }
        }, setup: function ( n, r, i ) {
            var s = e( this );
            if ( !a ) {
                s.on( "click", e.event.special.fastclick.handler );
                return
            }
            t = t.add( s );
            if ( !s.data( "objHandlers" ) ) {
                s.data( "objHandlers", { } );
                s.on( "touchstart", e.event.special.fastclick.touchstart );
                s.on( "touchend touchcancel", e.event.special.fastclick.handler )
            }
            if ( !o.ghostbuster ) {
                e( document ).on( "click vclick", u );
                o.ghostbuster = true
            }
        }, teardown: function ( n ) {
            var r = e( this );
            if ( !a ) {
                r.off( "click", e.event.special.fastclick.handler );
                return
            }
            t = t.not( r );
            r.off( "touchstart", e.event.special.fastclick.touchstart );
            r.off( "touchmove", e.event.special.fastclick.touchmove );
            r.off( "touchend touchcancel", e.event.special.fastclick.handler );
            if ( t.length == 0 ) {
                e( document ).off( "click vclick", u );
                o.ghostbuster = false
            }
        }, remove: function ( t ) {
            if ( !a ) {
                return
            }
            var n = e( this );
            delete n.data( "objHandlers" )[t.guid]
        }, handler: function ( t ) {
            var n = t.type;
            t.type = "fastclick";
            e.event.trigger.call( this, t, { }, this, true );
            t.type = n
        } }
} )( jQuery )
