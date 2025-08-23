$(function() {
    const cash = document.getElementById("COD");
    const creditCard = document.getElementById("credit-card");
    var stripe = Stripe('pk_test_51RFGzDGg0Fe7TJofHBe0SBc1jRBCDmvChNw03uLffEqBJBl6BytI8aYRaDBkw40calHVRiEev3OQG68jMwcYve8g00BgEyVHrM');
    const elements = stripe.elements({
        fonts: [
            {
                cssSrc: 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap'
            }
        ]
    })

    const cardNumberElement = elements.create('cardNumber', {
        style: {
            base: {
                color: '#555',
                fontFamily: 'Montserrat, sans-serif'
            }
        }
    })
    cardNumberElement.mount('#card-number-element')

    const cardExpiryElement = elements.create('cardExpiry', {
        style: {
            base: {
                color: '#555',
                fontFamily: 'Montserrat, sans-serif'
            }
        }
    })
    cardExpiryElement.mount('#card-expiry-element')

    const cardCVCElement = elements.create('cardCvc', {
        style: {
            base: {
                color: '#555',
                fontFamily: 'Montserrat, sans-serif'
            }
        }
    })
    cardCVCElement.mount('#card-cvc-element')

    cash.addEventListener('click', function () {
        creditCard.classList.remove('active');
        cash.classList.add('active');
        if ($('.panel').is(':visible')) {
            $('.panel').slideToggle('slow');
        } 
        $('#paymentMethod').val('COD');
    });

    creditCard.addEventListener('click', function () {
        cash.classList.remove('active');
        creditCard.classList.add('active');
        if (!$('.panel').is(':visible')) {
            $('.panel').slideToggle('slow');
        } 
        $('#paymentMethod').val('creditCard');
    });

    $("#checkout").one("click", function(){
        formSubmit();
    });

    async function formSubmit() {
        if ($('#paymentMethod').val() === 'creditCard') {
            await createToken();
        } 
        $("#form-checkout").submit();
    }

    function createToken() {
        var options = {
            cardholder_name: document.getElementById('cardholder-name').value,
            cardholder_cellphone: document.getElementById('cardholder-cellphone').value
        }

        document.getElementById('checkout').disabled = true;
        return stripe.createToken(cardNumberElement, options).then(function(result) {
            if(typeof result.error != 'undefined') {
                document.getElementById("checkout").disabled = false;
                alert(result.error.message);
            }

            if(typeof result.token != 'undefined') {
                document.getElementById("stripe-token-id").value = result.token.id;
            }
        });
    }
});