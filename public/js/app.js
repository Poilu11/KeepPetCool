var userCheckboxes = 
{
    checkBoxes : null,

    init: function()
    {
        userCheckboxes.checkboxes = document.querySelectorAll(".user-checkbox");

        for(var checkbox of userCheckboxes.checkboxes)
        {
            checkbox.addEventListener("click", userCheckboxes.handleClick);
        }
    },

    handleClick: function(e)
    {
        var target = e.currentTarget;
        var otherCheckbox = null;

        for(var checkbox of userCheckboxes.checkboxes)
        {
            if(checkbox != target)
            {
                otherCheckbox = checkbox;
            }
        }

        target.classList.add("criteria-active");
        otherCheckbox.classList.remove("criteria-active");
    },
}



var searchButton =
{
    init : function()
    {
        var button = document.querySelector("#searchButton");

        button.addEventListener('click', searchButton.handleClick);
    },

    handleClick: function(e)
    {
        var target = e.currentTarget;

        var relativeLink = target.dataset.route;

        // Le relative link dispose de deux tirets à la fin, ceux-ci gène pour concaténer les paramètres de la routes search. Je les retire grâce à substring.
        var length = relativeLink.length;
        relativeLink = relativeLink.substring(0, length-3);

        console.log(relativeLink);

        var userType = document.querySelector('.criteria-active').dataset.usertype;

        var city = document.querySelector('#zipcode').value.replace('-','_');

        var radius = document.querySelector('#radius').value;

        var latitude = document.querySelector('#search-latitude').value;

        var longitude = document.querySelector('#search-longitude').value;

        relativeLink += userType+'-'+city+'-'+radius+'-'+latitude+'+'+longitude;

        console.log(relativeLink);
        window.location.href = relativeLink;
    },

}














document.addEventListener('DOMContentLoaded', userCheckboxes.init);
document.addEventListener('DOMContentLoaded', searchButton.init);