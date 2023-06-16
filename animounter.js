if (!window.requestAnimationFrame) {
    window.requestAnimationFrame = (function () {
        return window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            window.oRequestAnimationFrame ||
            window.msRequestAnimationFrame ||
            function(callback, element) {
                window.setTimeout(callback, 1000 / 60);
            };
    })();
}

function Animounter(element, params) {
    params = params || {};

    this.element = element;

    this.currentValue = params.value || 0;

    this.duration = params.duration || 1000;

    this.durationLeft = this.duration;

    this.nextValue = this.currentValue;

    this.updateTimeout = 0;
}

Animounter.prototype = {
    getDifference: function () {
        return this.currentValue - this.nextValue;
    },

    getAbsoluteDifference: function () {
        return Math.abs(this.getDifference());
    },

    updateCurrentValue: function (value) {
        this.currentValue = value;

        this.render();
    },

    getOnePercentOfDuration: function () {
        return this.duration / 100;
    },

    getOffsetAbsoluteValue: function () {
        return Math.floor(this.getAbsoluteDifference() / this.getOnePercentOfDuration()) || 1;
    },

    getOffsetValue: function () {
        var offsetAbsoluteValue = this.getOffsetAbsoluteValue();

        var factor = this.getDifference() > 0 ? -1 : 1;

        return offsetAbsoluteValue * factor;
    },

    changeCurrentValue: function () {
        var offset = this.getOffsetValue();

        var newValue = this.currentValue + offset;

        this.updateCurrentValue(newValue);

        this.decreaseDurationLeft();
    },

    decreaseDurationLeft: function () {
        this.durationLeft = this.durationLeft - this.getDelay();
    },

    getDelay: function () {
        return this.durationLeft / this.getAbsoluteDifference();
    },

    update: function () {
        clearTimeout(this.updateTimeout);

        if (this.getDifference() === 0) {
            return;
        }

        this.changeCurrentValue();

        this.updateTimeout = setTimeout(function () {
            this.update();
        }.bind(this), this.getDelay());
    },

    reset: function (value) {
        this.nextValue = value;

        this.durationLeft = this.duration;
    },

    //public methods

    render: function () {
        requestAnimationFrame(function () {
            this.element.innerText = this.currentValue;
        }.bind(this));
    },

    change: function (value) {
        this.reset(value);

        this.update();
    }
};

