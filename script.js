var psyWidget = {
    ciientId: 111,
    host: 'http://apijs.injini.ru',
    elementsIds: {
        mainWindow: 'PsyMainWindow',
        background: 'PsyWidgetDarkBack'
    },
    elementsClasses: {
        button: 'psy-widget-button',
        background: 'psy-widget-dark-back',
        backgroundVisible: 'psy-widget-dark-back-show',
        mainWindow: 'psy-widget-main-window'
    },

    init: function () {
        console.log('init');
        this.addButton();
        this.loadCSS();
        this.addPanel();
    },

    loadCSS: function () {
        console.log('load css ');
        var ss = document.createElement('link');
        ss.type = "text/css";
        ss.rel = "stylesheet";
        ss.href = this.host + "/psy-widget-styles.css";
        document.getElementsByTagName('head')[0].appendChild(ss);
    },

    addButton: function () {
        let bodyElem = document.getElementsByTagName('body')[0];
        widgetButtonElem = document.createElement('button');
        widgetButtonElem.innerText = 'PSY';
        widgetButtonElem.classList.add(this.elementsClasses.button);
        widgetButtonElem.onclick = function () {
            psyWidget.showPanel();
        };
        bodyElem.append(widgetButtonElem);
    },

    addPanel: function () {
        let bodyElem = document.getElementsByTagName('body')[0];
        //create dark background
        let darkBackElem = document.createElement('div');
        darkBackElem.id = this.elementsIds.background;
        darkBackElem.classList.add(this.elementsClasses.background);
        darkBackElem.addEventListener('click', function () {
            psyWidget.hidePanel();
        });
        //create main window
        let mainWindowElem = document.createElement('div');
        mainWindowElem.id = this.elementsIds.mainWindow;
        mainWindowElem.classList.add(this.elementsClasses.mainWindow);
        darkBackElem.appendChild(mainWindowElem);
        bodyElem.appendChild(darkBackElem);
    },

    showPanel: function () {
        let darkBackElem = document.getElementById(this.elementsIds.background);
        darkBackElem.classList.add(this.elementsClasses.backgroundVisible);
    },

    hidePanel: function () {
        let darkBackElem = document.getElementById(this.elementsIds.background);
        darkBackElem.classList.remove(this.elementsClasses.backgroundVisible);
    },

    ////
    // ajax requests
    ////

    loadServiceList: function () {
        const data = {
            id: psyWidget.id,
            fake: 'fakeData'
        };
        let req = new XMLHttpRequest();
        req.open('GET', this.host + '/booking/service-list?id=' + psyWidget.id, false);
        req.setRequestHeader('Content-Type', 'Content-type', 'application/json; charset=utf-8');
        req.onreadystatechange = function () {
            if (req.readyState != 4 && req.status != 200) return;
            document.getElementById(psyWidget.elementsIds.mainWindow).innerHTML = req.responseText;
        }
        req.send(JSON.stringify(data));
    }
}
document.addEventListener('DOMContentLoaded', psyWidget.init());