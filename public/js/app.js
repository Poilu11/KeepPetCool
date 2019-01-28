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

        var userType = document.querySelector('.criteria-active').dataset.usertype;

        var adress = document.querySelector('#zipcode').value.replace(/ /g, '+');

        var radius = document.querySelector('#radius').value;

        var latitude = document.querySelector('#search-latitude').value;
        var longitude = document.querySelector('#search-longitude').value;

        relativeLink = relativeLink.replace('userType', userType);
        relativeLink = relativeLink.replace('adress', adress);
        relativeLink = relativeLink.replace('radius', radius);
        relativeLink = relativeLink.replace('latAndLong', latitude+'_'+longitude);


        console.log(relativeLink);
        window.location.href = relativeLink;
    },

}














document.addEventListener('DOMContentLoaded', userCheckboxes.init);
document.addEventListener('DOMContentLoaded', searchButton.init);