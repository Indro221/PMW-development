// i. Field data going to owner's whatsapp after submission__

// document.addEventListener('wpcf7mailsent', function(event) {
//     // 1. YOUR BUSINESS NUMBER (Receives the lead)
//     const myNumber = '..'; 
    
//     // 2. DATA EXTRACTION
//     const inputs = event.detail.inputs;
//     let message = "*-- New Pickup Request --*%0a%0a";
//     let foundData = false;

//     inputs.forEach(function(input) {
//         if (input.name.includes('qtf')) {
//             foundData = true;
//             let label = input.name.replace('qtf-', '').replace('qtf', '')
//                                   .replace(/-/g, ' ')
//                                   .replace(/\b\w/g, l => l.toUpperCase());
//             message += "*" + label + ":* " + encodeURIComponent(input.value) + "%0a";
//         }
//     });

//     // 3. DIFFERENT REDIRECT LOGIC FOR MOBILE AND DESKTOP
//     if (foundData) {
//     const whatsappUrl = "https://wa.me/" + myNumber + "?text=" + message;
//     // Detection: Is it a mobile device?
//     const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
//     if (isMobile) {
//         // Mobile: Redirect in the SAME tab is much more reliable
//         window.location.href = whatsappUrl;
//     } else {
//         // Desktop: Try new tab
//         const wpWindow = window.open(whatsappUrl, '_blank');
        
//         // Failsafe for Desktop Popup Blockers
//         if (!wpWindow || wpWindow.closed || typeof wpWindow.closed === 'undefined') {
//             window.location.href = whatsappUrl;
//         }
//     }
// 	}
// }, false);

// ii. All tel and name filed input filter (UX)__

document.addEventListener('input', function (e) {
    const t = e.target;
    
    // 1. Filter for all TEL fields (Numbers only)
    if (t.type === 'tel') {
        t.value = t.value.replace(/[^0-9]/g, '');
    }
    
    // 2. Filter for NAME field (Letters and Spaces only)
    if (t.classList.contains('qt_name')) {
        t.value = t.value.replace(/[^a-zA-Z\s]/g, '');
    }
}, false);

// iii. all DOM loaded script__

// 1. form logic- date field placeholder appear and disappear
document.addEventListener('DOMContentLoaded', function(){
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('qt_date')) {
        if (e.target.value !== "") {
            e.target.classList.add('has-value');
        } else {
            e.target.classList.remove('has-value');
        }
    }
});
// 2. form logic- When focused in that is calendar opened, placeholder disappears
document.addEventListener('focusin', function(e) {
    if (e.target.classList.contains('qt_date')) {
        e.target.classList.add('has-value');
    }
});
// 3. form logic- When focused out that is interaction done with field, placeholder remains if field is cleared or disappeares if filled
document.addEventListener('focusout', function(e) {
    if (e.target.classList.contains('qt_date') && e.target.value === "") {
        e.target.classList.remove('has-value');
    }
});
// 4. form logic- Visibility logic of waste description field
const dropdown = document.querySelector('.qt_type');
const actualInput = document.querySelector('.qt_wdesc');

if (dropdown && actualInput) {
    // Adding both change and input events ensures it triggers immediately on mobile devices
    ['change', 'input'].forEach(evt => {
        dropdown.addEventListener(evt, function() {
            if (this.value === 'Awareness Campaigns') {
                actualInput.value = '';        // Clears the value
                actualInput.style.display = 'none'; // Hides the input
            } else {
                actualInput.style.display = 'block'; // Shows the input
            }
        });
    });
}

// iv. On page and multipage navigation active state glitch fixing__
// 	const item1 = document.querySelector('#premium-nav-menu-item-39261');
//     const item3 = document.querySelector('#premium-nav-menu-item-27092');
//     const targetSection3 = document.querySelector('#services'); // Ensure exact ID of item1 and item3

//     // 1. MOBILE FIX: Highlighting Item-3 when its section is in view
//     if (targetSection3 && item3) {
//         const observer = new IntersectionObserver((entries) => {
//             entries.forEach(entry => {
//                 if (entry.isIntersecting) {
//                     document.querySelectorAll('.premium-active-item').forEach(el => el.classList.remove('premium-active-item'));
//                     item3.classList.add('premium-active-item');
//                 }
//             });
//         }, { rootMargin: '-30% 0px -30% 0px' });

//         observer.observe(targetSection3);
//     }

//     // 2. BEYOND REGION: Resetting to Item-1 when scrolling past everything
//     window.addEventListener("scroll", function() {
//         if (targetSection3) {
//             const sectionBottom = targetSection3.getBoundingClientRect().bottom;
            
//             // If scrolled completely past the target section
//             if (sectionBottom < 0) {
//                 document.querySelectorAll('.premium-active-item').forEach(el => el.classList.remove('premium-active-item'));
//                 if (item1) {
//                     item1.classList.add('premium-active-item');
//                 }
//             }
//         }
//     }, { passive: true });
	
});

// v. 
