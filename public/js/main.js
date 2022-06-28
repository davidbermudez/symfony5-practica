//const ourParallax = document.getElementById('parallax')
//const parallaxInstance = new Parallax(ourParallax)

const submit = document.getElementById('submitButton')
const inputEmail = document.getElementById('inputUsername')
const iconEmailRight = document.getElementById('emailCheckIconRight')
// form reset-password
const reset_password_request_form_email = document.getElementById('reset_password_request_form_email')
const MAXCHARNAMEFIELD = 20
const MINCHARNAMEFIELD = 3


// EVENT LISTENERS
document.addEventListener('change', event => {
    if (event.target.matches('.inputUsername')) {
        validateEmail(event.target.value, 'inputEmail')
    } else if (event.target.matches('.reset_password_request_form_email')) {
        validateEmail(event.target.value, 'reset_password_request_form_email')
    }
  }, false)


function validateEmail(value, item) {
    const iconEmailRight = document.getElementById('emailCheckIconRight')
    const emailParagraph = document.getElementById('emailActionHint')    
    console.log(value);
    console.log(item);
    input = document.getElementById(item)
    console.log(input);
    if (validateRegexString(value)) {
        // input box color
        input.classList.remove('is-danger')
        input.classList.add('is-success')
        // icon type
        iconEmailRight.classList.remove('fa-exclamation-triangle')
        iconEmailRight.classList.add('fa-check')
        emailParagraph.style = 'display:none'

        emailValidated = true
        submitCheck()
    } else {
        // input box color
        input.classList.remove('is-sucess')
        input.classList.add('is-danger')
        // icon type
        iconEmailRight.classList.remove('fa-check')
        iconEmailRight.classList.add('fa-exclamation-triangle')
        emailParagraph.style = 'display:block'

        emailValidated = false
    }
    console.log(emailValidated);
}

function submitCheck() {
    const emailParagraph = document.getElementById('emailActionHint')
    console.log(nameValidated, emailValidated)
    if (nameValidated && emailValidated) {

        submit.disabled = false;              //button is no longer no-clickable
        submit.removeAttribute("disabled");   //detto
    } else {
        emailParagraph.style = 'display:block'  //email warning shows up
    }
}

function validateRegexString(email) {
    const regexString = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i
    return regexString.test(String(email).toLowerCase()) // true|false
  }