var completion = {

    init: function() {
        console.log('init completion');

        var telephoneInput = document.querySelectorAll('.autoCompletion');
        
        telephoneInput.forEach(function(input) {
            input.addEventListener('keyup', completion.handleKeyup);
        });
        
    },

    handleKeyup: function(evt) {
        console.log('handleKeyup');

        var telephoneInput = evt.currentTarget;
        // console.log(telephoneInput);

        var telephoneInputValue = telephoneInput.value;
        // console.log(telephoneInputValue);

        var replaceValue = telephoneInputValue.replace(/(\d{2})\-?(\d{2})\-?(\d{2})\-?(\d{2})\-?(\d{2})/, '$1-$2-$3-$4-$5');
        // console.log(replaceValue); 

        telephoneInput.value = replaceValue;
  
    },

};

document.addEventListener('DOMContentLoaded', completion.init);
