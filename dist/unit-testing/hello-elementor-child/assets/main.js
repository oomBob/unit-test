/** 
 * Custom JS
 * oom_ss
*/

jQuery(document).ready(function($) {

	//Add Class to Set Proper Width
	$('.elementor-location-archive .elementor-element.e-parent').each(function() {
		$(this).addClass('oom-elementor');
	});
	$('.elementor-location-single .elementor-element.e-parent').each(function() {
		$(this).addClass('oom-elementor');
	});
	
	$('#oom_page_body .elementor-element.e-parent').each(function() {
		$(this).addClass('oom-elementor');
	});
	
    $("body").on("click", ".oom-close", function(){
        console.log('oom-close');
        $('.oom-modal').hide();
	});
	
	// Disable the first option in Elementor Form Select
	$('select.elementor-field-textual').each(function() {
      $(this).find('option:first').prop('disabled', true);
    });

    // Function to initialize datepickers for Q rental fields
    function initQRentalDatepickers() {
        var $qFrom = $('#field_q_rental_from_date');
        var $qTo = $('#field_q_rental_to_date');
        
        // Check if fields exist and jQuery UI Datepicker is available
        if (($qFrom.length || $qTo.length) && $.fn.datepicker) {
            console.log('Initializing Q rental datepickers...');
            
            // Minimum selectable date uses theme option (defaults to 2 days)
            var advanceDays = 2;
            if (typeof window.oom_ajax_obj !== 'undefined' && window.oom_ajax_obj.advanced_booking_days) {
                var parsed = parseInt(window.oom_ajax_obj.advanced_booking_days, 10);
                if (!isNaN(parsed) && parsed >= 0) {
                    advanceDays = parsed;
                }
            }
            var minSelectableDate = new Date();
            minSelectableDate.setDate(minSelectableDate.getDate() + advanceDays);

            // Ensure the datepicker is appended inside the popup and doesn't bubble clicks that close it
            function appendDpToPopup(input, inst) {
                var $container = $(input).closest('.elementor-popup-modal, .e-dialog-modal, .dialog-widget, .elementor-lightbox');
                if ($container.length) {
                    inst.dpDiv.appendTo($container);
                }
            }

            function bindDpStopPropagation(inst) {
                setTimeout(function () {
                    inst.dpDiv.off('.oomDp');
                    inst.dpDiv.on('mousedown.oomDp click.oomDp touchstart.oomDp', function (e) {
                        // Only stop propagation to prevent popup closure
                        if (e && typeof e.stopPropagation === 'function') {
                            e.stopPropagation();
                        }
                        // Don't prevent default - let datepicker handle its own events
                    });
                }, 0);
            }

            // Helper to parse dd-mm-yy to Date
            function parseDate(value) {
                try {
                    return $.datepicker.parseDate('dd-mm-yy', value);
                } catch (e) {
                    return null;
                }
            }

            // Utility: combine noWeekends with custom blockout dates
            var blockoutDates = (window.oom_ajax_obj && Array.isArray(window.oom_ajax_obj.blockout_dates)) ? window.oom_ajax_obj.blockout_dates : [];
            function isBlockoutDate(date) {
                var formatted = $.datepicker.formatDate('dd-mm-yy', date);
                return blockoutDates.indexOf(formatted) !== -1;
            }
            function beforeShowDayCombined(date) {
                // First, block weekends
                var noWeekend = $.datepicker.noWeekends(date);
                if (!noWeekend[0]) return [false, '', 'Weekend'];
                // Then, block custom dates
                if (isBlockoutDate(date)) {
                    return [false, 'blockout-date', 'Blocked'];
                }
                return [true, '', ''];
            }

            // Init From datepicker for Q fields
            if ($qFrom.length) {
                // Destroy existing datepicker if it exists to prevent duplicates
                if ($qFrom.hasClass('hasDatepicker')) {
                    $qFrom.datepicker('destroy');
                }
                
                $qFrom.datepicker({
                    dateFormat: 'dd-mm-yy',
                    minDate: minSelectableDate,
                    changeMonth: true,
                    changeYear: true,
                    beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
                    beforeShowDay: beforeShowDayCombined,
                    onSelect: function(dateText) {
                        var selectedDateQ = parseDate(dateText) || minSelectableDate;
                        if ($qTo.length && $qTo.hasClass('hasDatepicker')) {
                            $qTo.datepicker('option', 'minDate', selectedDateQ);
                            var toValQ = $qTo.val();
                            if (toValQ) {
                                var toDateQ = parseDate(toValQ);
                                if (toDateQ && toDateQ < selectedDateQ) {
                                    $qTo.val('');
                                }
                            }
                        } else if ($qTo.length) {
                            // Destroy existing datepicker if it exists
                            if ($qTo.hasClass('hasDatepicker')) {
                                $qTo.datepicker('destroy');
                            }
                            $qTo.datepicker({
                                dateFormat: 'dd-mm-yy',
                                minDate: selectedDateQ,
                                changeMonth: true,
                                changeYear: true,
                                beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
                                beforeShowDay: beforeShowDayCombined
                            });
                        }
                    }
                });
                console.log('Q From datepicker initialized');
            }

            // Init To datepicker for Q fields
            if ($qTo.length) {
                // Destroy existing datepicker if it exists to prevent duplicates
                if ($qTo.hasClass('hasDatepicker')) {
                    $qTo.datepicker('destroy');
                }
                
                $qTo.datepicker({
                    dateFormat: 'dd-mm-yy',
                    minDate: minSelectableDate,
                    changeMonth: true,
                    changeYear: true,
                    beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
                    beforeShowDay: beforeShowDayCombined
                });
                console.log('Q To datepicker initialized');
            }
        } else {
            console.log('Q rental fields not found or jQuery UI Datepicker not available');
        }
    }

    // Function to handle time validation for Q rental fields
    function initQRentalTimeValidation() {
        var $qFromDate = $('#field_q_rental_from_date');
        var $qToDate = $('#field_q_rental_to_date');
        var $qFromTime = $('#field_q_rental_from_time');
        var $qToTime = $('#field_q_rental_to_time');
        
        // Check if all required fields exist
        if (!$qFromDate.length || !$qToDate.length || !$qFromTime.length || !$qToTime.length) {
            return;
        }
        
        // Helper function to parse time string (e.g., "10:30am" to minutes since midnight)
        function parseTimeToMinutes(timeStr) {
            if (!timeStr) return 0;
            
            var match = timeStr.match(/^(\d{1,2}):(\d{2})(am|pm)$/i);
            if (!match) return 0;
            
            var hours = parseInt(match[1], 10);
            var minutes = parseInt(match[2], 10);
            var period = match[3].toLowerCase();
            
            if (period === 'pm' && hours !== 12) {
                hours += 12;
            } else if (period === 'am' && hours === 12) {
                hours = 0;
            }
            
            return hours * 60 + minutes;
        }
        
        // Helper function to format minutes back to time string
        function formatMinutesToTime(minutes) {
            if (minutes <= 0) return '';
            
            var hours = Math.floor(minutes / 60);
            var mins = minutes % 60;
            var period = hours >= 12 ? 'pm' : 'am';
            
            if (hours > 12) {
                hours -= 12;
            } else if (hours === 0) {
                hours = 12;
            }
            
            return hours.toString().padStart(2, '0') + ':' + mins.toString().padStart(2, '0') + period;
        }
        
        // Function to update time options based on selected dates
        function updateTimeOptions() {
            var fromDate = $qFromDate.val();
            var toDate = $qToDate.val();
            
            // If both dates are the same, apply time restrictions
            if (fromDate && toDate && fromDate === toDate) {
                var fromTimeValue = $qFromTime.val();
                var fromTimeMinutes = parseTimeToMinutes(fromTimeValue);
                
                // Get all time options from the to-time select
                var $toTimeOptions = $qToTime.find('option');
                
                // Disable options that are before or equal to the from time
                $toTimeOptions.each(function() {
                    var $option = $(this);
                    var optionValue = $option.val();
                    
                    if (!optionValue) {
                        // Keep "Select Time" option enabled
                        $option.prop('disabled', false);
                        return;
                    }
                    
                    var optionMinutes = parseTimeToMinutes(optionValue);
                    
                    if (optionMinutes <= fromTimeMinutes) {
                        $option.prop('disabled', true);
                        // If this option was previously selected, clear the selection
                        if ($qToTime.val() === optionValue) {
                            $qToTime.val('');
                        }
                    } else {
                        $option.prop('disabled', false);
                    }
                });
            } else {
                // If dates are different, enable all time options
                $qToTime.find('option').prop('disabled', false);
            }
        }
        
        // Bind events to trigger time validation
        $qFromDate.on('change', updateTimeOptions);
        $qToDate.on('change', updateTimeOptions);
        $qFromTime.on('change', updateTimeOptions);
        
        // Initial call to set up time validation
        updateTimeOptions();
    }

    // Initial call to initialize datepickers
    initQRentalDatepickers();
    
    // Initial call to initialize time validation
    initQRentalTimeValidation();

    // Make the functions globally available for manual re-initialization
    window.initQRentalDatepickers = initQRentalDatepickers;
    window.initQRentalTimeValidation = initQRentalTimeValidation;

    // Set up MutationObserver to watch for dynamically added Q rental fields
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.id === 'field_q_rental_from_date' || node.id === 'field_q_rental_to_date' ||
                                node.querySelector && (node.querySelector('#field_q_rental_from_date') || node.querySelector('#field_q_rental_to_date'))) {
                                shouldReinit = true;
                            }
                        }
                    });
                }
            });
            if (shouldReinit) {
                console.log('Q rental fields detected, reinitializing datepickers and time validation...');
                setTimeout(function() {
                    initQRentalDatepickers();
                    initQRentalTimeValidation();
                }, 100); // Small delay to ensure DOM is ready
            }
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Initialize jQuery UI Datepickers for rental fields
    var $rentalFrom = $('#field_c_rental_from');
    var $rentalTo = $('#field_c_rental_to');
    var $qFrom = $('#field_q_rental_from_date');
    var $qTo = $('#field_q_rental_to_date');
    if (($rentalFrom.length || $rentalTo.length || $qFrom.length || $qTo.length) && $.fn.datepicker) {
      // Minimum selectable date uses theme option (defaults to 2 days)
      var advanceDays = 2;
      if (typeof window.oom_ajax_obj !== 'undefined' && window.oom_ajax_obj.advanced_booking_days) {
        var parsed = parseInt(window.oom_ajax_obj.advanced_booking_days, 10);
        if (!isNaN(parsed) && parsed >= 0) {
          advanceDays = parsed;
        }
      }
      var minSelectableDate = new Date();
      minSelectableDate.setDate(minSelectableDate.getDate() + advanceDays);

      // Ensure the datepicker is appended inside the popup and doesn't bubble clicks that close it
      function appendDpToPopup(input, inst) {
        var $container = $(input).closest('.elementor-popup-modal, .e-dialog-modal, .dialog-widget, .elementor-lightbox');
        if ($container.length) {
          inst.dpDiv.appendTo($container);
        }
      }

      function bindDpStopPropagation(inst) {
        setTimeout(function () {
          inst.dpDiv.off('.oomDp');
          inst.dpDiv.on('mousedown.oomDp click.oomDp touchstart.oomDp', function (e) {
            e.stopPropagation();
          });
        }, 0);
      }

      // Helper to parse dd-mm-yy to Date
      function parseDate(value) {
        try {
          return $.datepicker.parseDate('dd-mm-yy', value);
        } catch (e) {
          return null;
        }
      }

      // Utility: combine noWeekends with custom blockout dates
      var blockoutDates = (window.oom_ajax_obj && Array.isArray(window.oom_ajax_obj.blockout_dates)) ? window.oom_ajax_obj.blockout_dates : [];
      function isBlockoutDate(date) {
        var formatted = $.datepicker.formatDate('dd-mm-yy', date);
        return blockoutDates.indexOf(formatted) !== -1;
      }
      function beforeShowDayCombined(date) {
        // First, block weekends
        var noWeekend = $.datepicker.noWeekends(date);
        if (!noWeekend[0]) return [false, '', 'Weekend'];
        // Then, block custom dates
        if (isBlockoutDate(date)) {
          return [false, 'blockout-date', 'Blocked'];
        }
        return [true, '', ''];
      }

      // Init From datepicker
      if ($rentalFrom.length) {
        $rentalFrom.datepicker({
          dateFormat: 'dd-mm-yy',
          minDate: minSelectableDate,
          changeMonth: true,
          changeYear: true,
          beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
          beforeShowDay: beforeShowDayCombined,
          onSelect: function(dateText) {
            var selectedDate = parseDate(dateText) || minSelectableDate;
            // For monthly rental, prevent same-day return by setting min date to next day
            var minToDate = new Date(selectedDate);
            minToDate.setDate(minToDate.getDate() + 1);
            
            if ($rentalTo.length && $rentalTo.hasClass('hasDatepicker')) {
              $rentalTo.datepicker('option', 'minDate', minToDate);
              // If current To value is before new min, clear it
              var toVal = $rentalTo.val();
              if (toVal) {
                var toDate = parseDate(toVal);
                if (toDate && toDate < minToDate) {
                  $rentalTo.val('');
                }
              }
            } else if ($rentalTo.length) {
              $rentalTo.datepicker({
                dateFormat: 'dd-mm-yy',
                minDate: minToDate,
                changeMonth: true,
                changeYear: true,
                beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
                beforeShowDay: beforeShowDayCombined
              });
            }
          }
        });
      }

      // Init To datepicker
      if ($rentalTo.length && !$rentalTo.hasClass('hasDatepicker')) {
        // For monthly rental, set min date to next day to prevent same-day returns
        var minToDateInitial = new Date(minSelectableDate);
        minToDateInitial.setDate(minToDateInitial.getDate() + 1);
        
        $rentalTo.datepicker({
          dateFormat: 'dd-mm-yy',
          minDate: minToDateInitial,
          changeMonth: true,
          changeYear: true,
          beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
          beforeShowDay: beforeShowDayCombined
        });
      }

      // Init From datepicker for Q fields
      if ($qFrom.length) {
        $qFrom.datepicker({
          dateFormat: 'dd-mm-yy',
          minDate: minSelectableDate,
          changeMonth: true,
          changeYear: true,
          beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
          beforeShowDay: beforeShowDayCombined,
          onSelect: function(dateText) {
            var selectedDateQ = parseDate(dateText) || minSelectableDate;
            if ($qTo.length && $qTo.hasClass('hasDatepicker')) {
              $qTo.datepicker('option', 'minDate', selectedDateQ);
              var toValQ = $qTo.val();
              if (toValQ) {
                var toDateQ = parseDate(toValQ);
                if (toDateQ && toDateQ < selectedDateQ) {
                  $qTo.val('');
                }
              }
            } else if ($qTo.length) {
              $qTo.datepicker({
                dateFormat: 'dd-mm-yy',
                minDate: selectedDateQ,
                changeMonth: true,
                changeYear: true,
                beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
                beforeShowDay: beforeShowDayCombined
              });
            }
          }
        });
      }

      // Init To datepicker for Q fields
      if ($qTo.length && !$qTo.hasClass('hasDatepicker')) {
        $qTo.datepicker({
          dateFormat: 'dd-mm-yy',
          minDate: minSelectableDate,
          changeMonth: true,
          changeYear: true,
          beforeShow: function (input, inst) { appendDpToPopup(input, inst); bindDpStopPropagation(inst); },
          beforeShowDay: beforeShowDayCombined
        });
      }
    }
	
});

jQuery(document).ready(function($){
	//Mobile Slide Menu
	const menu = new MmenuLight(document.querySelector("#oom-mobile-menu"), "all");
	const navigator = menu.navigation({
		selectedClass: 'Selected',
		slidingSubmenus: true,
		theme: 'light',
		navbars		: {
			content: ['', 'prev', 'breadcrumbs', 'close'] 
		},
		title: 'Menu'
	});
	const drawer = menu.offcanvas({
		position: 'left'
	});

	document
		.querySelector('a[href="#oom-mobile-menu"]')
		.addEventListener("click", (evnt) => {
		evnt.preventDefault();
		drawer.open();
	});
	
});

document.addEventListener('DOMContentLoaded', function() {
    const bookNowBtn = document.querySelector('.oom-nav-book-btn');
    const dropdownMenu = document.querySelector('.oom-nav-book-dropdown-menu');
    if (!bookNowBtn || !dropdownMenu) return;
    const parent = bookNowBtn.parentElement;
    let hideTimeout;

    document.addEventListener('click', function(event) {
        if (bookNowBtn.contains(event.target)) {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        } else if (!parent.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });

    dropdownMenu.addEventListener('mouseleave', function() {
        hideTimeout = setTimeout(function() {
            dropdownMenu.style.display = 'none';
        }, 100);
    });

    dropdownMenu.addEventListener('mouseenter', function() {
        if (hideTimeout) {
            clearTimeout(hideTimeout);
            hideTimeout = null;
        }
    });
}); 


//Monthly Rental Tweaks
document.addEventListener("DOMContentLoaded", function () {
  const input = document.querySelector("#field_c_vehicle_category");
  if (!input) return;

  // Wrap input + clear
  const wrapper = document.createElement("div");
  wrapper.classList.add("input-with-clear");
  input.parentNode.insertBefore(wrapper, input);
  wrapper.appendChild(input);

  const clearBtn = document.createElement("span");
  clearBtn.innerHTML = "&times;";
  clearBtn.className = "clear-btn";
  wrapper.appendChild(clearBtn);

  input.setAttribute("readonly", true);
  input.setAttribute("disabled", false);
  input.removeAttribute("disabled");

  function updateClearBtn() {
    clearBtn.style.display = input.value ? "block" : "none";
  }

  function addToInput(title) {
    if (!title) return;
    let currentValues = input.value
      .split(",")
      .map(t => t.trim())
      .filter(Boolean);

    if (!currentValues.includes(title)) {
      currentValues.push(title);
      input.value = currentValues.join(", ");
    }

    updateClearBtn();
    
    // Clear any error state when vehicle is selected
    if (typeof window.clearVehicleCategoryError === 'function') {
      window.clearVehicleCategoryError();
    }
  }

  function removeFromInput(title) {
    if (!title) return;
    let currentValues = input.value
      .split(",")
      .map(t => t.trim())
      .filter(Boolean);

    currentValues = currentValues.filter(t => t !== title);
    input.value = currentValues.join(", ");

    updateClearBtn();
  }

  // Toggle on .oom-checked-rfq
  document.querySelectorAll(".oom-checked-rfq").forEach(toggleEl => {
    toggleEl.addEventListener("click", function () {
      const listingItem = toggleEl.closest(".jet-listing-grid__item");

      const titleEl = listingItem?.querySelector(".oom-rental-loop-title h2");
      if (!titleEl) return;

      const title = titleEl.textContent.trim();
      const isActive = toggleEl.classList.toggle("active");

      if (isActive) {
        addToInput(title);
      } else {
        removeFromInput(title);
      }
    });
  });

  // On .oom-rfq-btn click: add category (titleElTwo), and toggle .oom-checked-rfq if not active
  document.querySelectorAll(".oom-rfq-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      const listingItem = btn.closest(".jet-listing-grid__item");
      if (!listingItem) return;

      const toggleEl = listingItem.querySelector(".oom-checked-rfq");

      // Get category (e.g. "Commercial Vehicles")
      const titleElTwo = listingItem.querySelector(".oom-rental-loop-title h2.jet-listing-dynamic-field__content");
      if (titleElTwo) {
        const titleTwo = titleElTwo.textContent.trim();
        addToInput(titleTwo);
      }

      // Now activate .oom-checked-rfq if not already active
      if (toggleEl && !toggleEl.classList.contains("active")) {
        toggleEl.classList.add("active");

        // Get vehicle title (e.g. "HONDA CRV 6")
        const titleEl = listingItem.querySelector(".oom-rental-loop-title h2");
        if (titleEl) {
          const title = titleEl.textContent.trim();
          addToInput(title);
        }
      }
    });
  });

  // Clear all
  clearBtn.addEventListener("click", function () {
    input.value = "";

    document.querySelectorAll(".oom-checked-rfq.active").forEach(el => {
      el.classList.remove("active");
    });

    updateClearBtn();
    
    // Clear any error state when field is cleared
    if (typeof window.clearVehicleCategoryError === 'function') {
      window.clearVehicleCategoryError();
    }
  });

  updateClearBtn();
});



document.addEventListener("DOMContentLoaded", function () {
  const select = document.getElementById("field_c_type_of_rental");
  const tabButtons = document.querySelectorAll(".oom-p-c-tab .e-n-tab-title");

  if (!select || tabButtons.length === 0) return;

  function updateSelectFromActiveTab() {
    const activeBtn = Array.from(tabButtons).find(btn => btn.getAttribute("aria-selected") === "true");
    if (!activeBtn) return;

    const tabIndex = activeBtn.getAttribute("data-tab-index");
    if (tabIndex === "1") {
      select.value = "Personal";
    } else if (tabIndex === "2") {
      select.value = "Corporate";
    }
  }

  // Initial sync on page load
  updateSelectFromActiveTab();

  // Add click listener to tabs to update select on tab change
  tabButtons.forEach(btn => {
    btn.addEventListener("click", function () {
      // Update aria-selected attributes for accessibility and active state
      tabButtons.forEach(b => b.setAttribute("aria-selected", "false"));
      btn.setAttribute("aria-selected", "true");

      updateSelectFromActiveTab();
    });
  });
});


document.querySelectorAll('.monthly-popup a.elementor-button, .daily-popup a.elementor-button').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault(); // this is what you're trying to remove
    // maybe some popup opening logic here
  });
});
	  
document.addEventListener("DOMContentLoaded", function () {
    const infoBar = document.getElementById('oom-info-bar');
    const closeBtn = document.getElementById('oom-close-info-bar');

    // Check if info bar was closed earlier in this session
    if (sessionStorage.getItem('oomInfoBarClosed') === 'true') {
        if (infoBar) infoBar.remove();
        return;
    }

    if (closeBtn && infoBar) {
        closeBtn.addEventListener('click', function () {
            infoBar.remove();
            sessionStorage.setItem('oomInfoBarClosed', 'true');
        });
    }
});
	  
// Randomise  
jQuery(document).ready(function($) {
	var $carousel = $('.oom-home-carousel .swiper');
	if (!$carousel.length) return;

	var swiperInstance = $carousel[0].swiper;
	if (swiperInstance) swiperInstance.destroy(true, true);

	var $wrapper = $carousel.find('.swiper-wrapper');

	// Get original slides only
	var $slides = $wrapper.children('.swiper-slide').not('.swiper-slide-duplicate');

	// Convert to array for shuffle
	var slidesArr = $slides.toArray();

	// Shuffle function
	function shuffle(array) {
		for (let i = array.length - 1; i > 0; i--) {
		let j = Math.floor(Math.random() * (i + 1));
		[array[i], array[j]] = [array[j], array[i]];
		}
		return array;
	}

	var shuffled = shuffle(slidesArr);

	// Re-assign data-slide and data-swiper-slide-index
	shuffled.forEach(function(slide, index) {
		slide.setAttribute('data-slide', index + 1);
		slide.setAttribute('data-swiper-slide-index', index);
	});

	// Clear wrapper and append shuffled slides (without duplicates)
	$wrapper.empty().append(shuffled);

	// Re-init swiper with same params
	var newSwiper = new Swiper($carousel[0], swiperInstance.params);

	// Show carousel after shuffling is complete
	$carousel.addClass('swiper-initialized');
});

// Postcode fields: enforce 6 numeric digits (works for dynamically added fields)
(function () {
  var selectors = '#field_c_postcode, #field_q_post_code';

  function sanitizeToSixDigits(value) {
    return String(value || '').replace(/\D/g, '').slice(0, 6);
  }

  function applyPostcodeAttributes(el) {
    if (!el) return;
    el.setAttribute('inputmode', 'numeric');
    el.setAttribute('pattern', '\\d{6}');
    el.setAttribute('min', '0');
    el.setAttribute('max', '999999');
  }

  // Initial pass on DOM ready
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll(selectors).forEach(function (el) {
      applyPostcodeAttributes(el);
      var cleaned = sanitizeToSixDigits(el.value);
      if (el.value !== cleaned) el.value = cleaned;
    });
  });

  // Delegate input events so it also works when fields are injected later
  document.addEventListener(
    'input',
    function (event) {
      var target = event.target;
      if (!(target instanceof Element)) return;
      if (!target.matches(selectors)) return;

      applyPostcodeAttributes(target);
      var cleaned = sanitizeToSixDigits(target.value);
      if (target.value !== cleaned) {
        target.value = cleaned;
      }
    },
    true
  );
})();


// Fallback for dynamically added Q rental fields
jQuery(document).on('focus', '#field_q_rental_from_date, #field_q_rental_to_date', function () {
  var $ = jQuery;
  if (!$.fn.datepicker) return;
  
  // Check if datepicker is already initialized and working
  if (jQuery(this).hasClass('hasDatepicker')) {
    // If already initialized, just show it instead of reinitializing
    try {
      jQuery(this).datepicker('show');
    } catch (e) {
      console.warn('Error showing existing datepicker:', e);
      // If there's an error, remove the broken datepicker and reinitialize
      jQuery(this).removeClass('hasDatepicker').datepicker('destroy');
    }
    return;
  }
  
  // Also initialize time validation for dynamically added fields
  setTimeout(function() {
    if (typeof window.initQRentalTimeValidation === 'function') {
      window.initQRentalTimeValidation();
    }
  }, 50);
  
  // Minimum selectable date uses theme option (defaults to 2 days)
  var advanceDays = 2;
  if (typeof window.oom_ajax_obj !== 'undefined' && window.oom_ajax_obj.advanced_booking_days) {
    var parsed = parseInt(window.oom_ajax_obj.advanced_booking_days, 10);
    if (!isNaN(parsed) && parsed >= 0) {
      advanceDays = parsed;
    }
  }
  var minSelectableDate = new Date();
  minSelectableDate.setDate(minSelectableDate.getDate() + advanceDays);

  // Enhanced mobile detection
  var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                 (window.innerWidth <= 768) || 
                 ('ontouchstart' in window);

  // Ensure the datepicker is appended inside the popup and doesn't bubble clicks that close it
  function appendDpToPopup(input, inst) {
    // Safety check for parameters
    if (!input || !inst || !inst.dpDiv) {
      console.warn('appendDpToPopup: Missing required parameters', { input: input, inst: inst });
      return;
    }
    
    var $container = jQuery(input).closest('.elementor-popup-modal, .e-dialog-modal, .dialog-widget, .elementor-lightbox, .jet-popup__container, .jet-popup__container-inner');
    if ($container.length) {
      inst.dpDiv.appendTo($container);
      
      // Mobile-specific positioning adjustments
      if (isMobile) {
        var inputRect = input.getBoundingClientRect();
        var containerRect = $container[0].getBoundingClientRect();
        var viewportHeight = window.innerHeight;
        
        // Calculate optimal position for mobile
        var topPosition = Math.min(inputRect.bottom + 5, viewportHeight - 300);
        var leftPosition = Math.max(10, Math.min(inputRect.left, containerRect.width - 300));
        
        inst.dpDiv.css({
          'position': 'fixed',
          'top': topPosition + 'px',
          'left': leftPosition + 'px',
          'z-index': '999999999',
          'max-width': '300px',
          'width': '300px'
        });
      }
    }
  }

  function bindDpStopPropagation(inst) {
    // Safety check for inst parameter
    if (!inst || !inst.dpDiv) {
      console.warn('bindDpStopPropagation: Missing required inst parameter', { inst: inst });
      return;
    }
    
    setTimeout(function () {
      inst.dpDiv.off('.oomDp');
      
      // Enhanced event handling for mobile
      var events = isMobile ? 
        'mousedown.oomDp click.oomDp touchstart.oomDp touchend.oomDp touchmove.oomDp' : 
        'mousedown.oomDp click.oomDp touchstart.oomDp';
      
      inst.dpDiv.on(events, function (e) {
        // Only stop propagation to prevent popup closure
        if (e && typeof e.stopPropagation === 'function') {
          e.stopPropagation();
        }
        // Don't prevent default - let datepicker handle its own events
      });
      
      // Additional mobile-specific event handling
      if (isMobile) {
        inst.dpDiv.on('touchstart.oomDp', function(e) {
          // Only stop propagation to prevent popup closure
          if (e && typeof e.stopPropagation === 'function') {
            e.stopPropagation();
          }
          // Don't prevent default - let datepicker handle its own events
        });
        
        // Prevent scrolling when interacting with datepicker
        inst.dpDiv.on('touchmove.oomDp', function(e) {
          // Only stop propagation to prevent popup closure
          if (e && typeof e.stopPropagation === 'function') {
            e.stopPropagation();
          }
          // Don't prevent default - let datepicker handle its own events
        });
      }
    }, 0);
  }

  // Helper to parse dd-mm-yy to Date
  function parseDate(value) {
    try {
      return $.datepicker.parseDate('dd-mm-yy', value);
    } catch (e) {
      return null;
    }
  }

  // Utility: combine noWeekends with custom blockout dates
  var blockoutDates = (window.oom_ajax_obj && Array.isArray(window.oom_ajax_obj.blockout_dates)) ? window.oom_ajax_obj.blockout_dates : [];
  function isBlockoutDate(date) {
    var formatted = $.datepicker.formatDate('dd-mm-yy', date);
    return blockoutDates.indexOf(formatted) !== -1;
  }
  function beforeShowDayCombined(date) {
    // First, block weekends
    var noWeekend = $.datepicker.noWeekends(date);
    if (!noWeekend[0]) return [false, '', 'Weekend'];
    // Then, block custom dates
    if (isBlockoutDate(date)) {
      return [false, 'blockout-date', 'Blocked'];
    }
    return [true, '', ''];
  }

  var $this = jQuery(this);
  var isFromField = $this.attr('id') === 'field_q_rental_from_date';
  var $qFrom = jQuery('#field_q_rental_from_date');
  var $qTo = jQuery('#field_q_rental_to_date');

  try {
    $this.datepicker({
      dateFormat: 'dd-mm-yy',
      minDate: isFromField ? minSelectableDate : (function() {
        var fromVal = $qFrom.val();
        if (fromVal) {
          var fromDate = parseDate(fromVal);
          return fromDate || minSelectableDate;
        }
        return minSelectableDate;
      })(),
      changeMonth: true,
      changeYear: true,
      showButtonPanel: false,
      closeText: '',
      currentText: '',
      monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
      monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
      dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
      beforeShow: function (input, inst) { 
        try {
          // Safety check for inst parameter
          if (input && inst && typeof inst === 'object' && inst.dpDiv) {
            appendDpToPopup(input, inst); 
            bindDpStopPropagation(inst); 
          } else {
            console.warn('beforeShow: Invalid inst parameter', { input: input, inst: inst });
          }
        } catch (e) {
          console.error('Error in beforeShow callback:', e);
        }
      },
      beforeShowDay: beforeShowDayCombined,
      onSelect: function(dateText, inst) {
        try {
          // Safety check for inst parameter
          if (!dateText) return;
          
          if (isFromField) {
            var selectedDate = parseDate(dateText) || minSelectableDate;
            if ($qTo.length && $qTo.hasClass('hasDatepicker')) {
              $qTo.datepicker('option', 'minDate', selectedDate);
              // If current To value is before new min, clear it
              var toVal = $qTo.val();
              if (toVal) {
                var toDate = parseDate(toVal);
                if (toDate && toDate < selectedDate) {
                  $qTo.val('');
                }
              }
            }
          }
          
          // Force close on mobile to prevent UI issues
          if (isMobile) {
            setTimeout(function() {
              try {
                $this.datepicker('hide');
              } catch (e) {
                console.warn('Error hiding datepicker:', e);
              }
            }, 100);
          }
        } catch (e) {
          console.error('Error in onSelect callback:', e);
        }
      }
    });
    
    // Mark as successfully initialized
    $this.addClass('oom-datepicker-initialized');
    
  } catch (e) {
    console.error('Error initializing datepicker:', e);
    // Remove any broken datepicker classes
    $this.removeClass('hasDatepicker oom-datepicker-initialized');
  }
  
  // Mobile-specific enhancements
  if (isMobile) {
    // Add mobile-friendly styling
    $this.addClass('mobile-date-input');
    
    // Make field readonly to prevent keyboard on mobile
    $this.attr('readonly', 'readonly');
    
    // Enhanced touch handling to prevent keyboard and ensure proper focus
    $this.on('touchstart', function(e) {
      // Only stop propagation to prevent popup closure
      if (e && typeof e.stopPropagation === 'function') {
        e.stopPropagation();
      }
      // Don't prevent default - let the field handle its own events
      
      // Prevent keyboard from showing
      this.blur();
      
      // Trigger datepicker manually
      setTimeout(() => {
        $(this).datepicker('show');
      }, 10);
    });
    
    // Also handle click events to prevent keyboard
    $this.on('click', function(e) {
      // Don't prevent default - let the field handle its own events
      this.blur();
      
      // Trigger datepicker manually
      setTimeout(() => {
        $(this).datepicker('show');
      }, 10);
    });
    
    // Prevent focus events that might trigger keyboard
    $this.on('focus', function(e) {
      // Don't prevent default - let the field handle its own events
      this.blur();
    });
  }
});

// Prevent clicks inside datepicker from closing popups
jQuery(document).on('mousedown click touchstart touchend touchmove', '.ui-datepicker', function (e) {
  // Stop propagation to prevent popup closure
  if (e && typeof e.stopPropagation === 'function') {
    e.stopPropagation();
  }
  // Don't prevent default - let datepicker work normally
});

// Additional mobile-specific datepicker safeguards
jQuery(document).on('DOMNodeInserted', function(e) {
  try {
    // Ensure e.target exists and is a valid element
    if (!e || !e.target || !e.target.className) {
      return;
    }
    
    // Safely check if className exists and contains 'ui-datepicker'
    var className = e.target.className;
    var hasDatepickerClass = false;
    
    // Handle both string and DOMTokenList cases
    if (className) {
      if (typeof className === 'string') {
        hasDatepickerClass = className.indexOf('ui-datepicker') !== -1;
      } else if (className.contains && typeof className.contains === 'function') {
        // Handle DOMTokenList (modern browsers)
        hasDatepickerClass = className.contains('ui-datepicker');
      }
    }
    
    if (hasDatepickerClass) {
      var $datepicker = jQuery(e.target);
      
      // Only set z-index to ensure datepicker is above other elements
      $datepicker.css({
        'z-index': '999999999'
      });
      
      // Prevent body scroll on mobile when datepicker is open
      jQuery('body').addClass('datepicker-open');
      
      // Only prevent events from bubbling to parent elements (prevents popup closure)
      $datepicker.on('mousedown click touchstart touchend touchmove', function(e) {
        // Stop propagation to prevent popup closure
        if (e && typeof e.stopPropagation === 'function') {
          e.stopPropagation();
        }
        // Don't prevent default - let datepicker work normally
      });
    }
  } catch (error) {
    // Log error but don't break the page
    console.warn('Error in datepicker DOMNodeInserted handler:', error);
  }
});

// Additional mobile keyboard prevention for date fields
jQuery(document).ready(function() {
  var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                 (window.innerWidth <= 768) || 
                 ('ontouchstart' in window);
  
  if (isMobile) {
    // Ensure date fields are readonly on mobile to prevent keyboard
    jQuery('#field_q_rental_from_date, #field_q_rental_to_date').each(function() {
      var $field = jQuery(this);
      
      // Make readonly if not already
      if (!$field.attr('readonly')) {
        $field.attr('readonly', 'readonly');
      }
      
      // Add mobile class
      $field.addClass('mobile-date-input');
      
      // Prevent any focus that might trigger keyboard
      $field.on('focus', function(e) {
        // Don't prevent default - let the field handle its own events
        this.blur();
        return false;
      });
      
      // Handle touch events
      $field.on('touchstart', function(e) {
        // Only stop propagation to prevent popup closure
        if (e && typeof e.stopPropagation === 'function') {
          e.stopPropagation();
        }
        // Don't prevent default - let the field handle its own events
        this.blur();
        
        // Show datepicker if it exists and is working
        if ($field.hasClass('hasDatepicker')) {
          try {
            setTimeout(() => {
              $field.datepicker('show');
            }, 10);
          } catch (e) {
            console.warn('Error showing datepicker:', e);
            // If there's an error, try to reinitialize
            if ($field.hasClass('oom-datepicker-initialized')) {
              $field.removeClass('hasDatepicker oom-datepicker-initialized');
              // Trigger focus event to reinitialize
              $field.trigger('focus');
            }
          }
        }
      });
    });
  }
});

// Cleanup function to remove broken datepicker instances
function cleanupBrokenDatepickers() {
  jQuery('#field_q_rental_from_date, #field_q_rental_to_date').each(function() {
    var $field = jQuery(this);
    
    // Check if datepicker is broken
    if ($field.hasClass('hasDatepicker') && !$field.hasClass('oom-datepicker-initialized')) {
      try {
        $field.datepicker('destroy');
        $field.removeClass('hasDatepicker');
        console.log('Cleaned up broken datepicker for:', $field.attr('id'));
      } catch (e) {
        console.warn('Error cleaning up datepicker:', e);
        $field.removeClass('hasDatepicker');
      }
    }
  });
}

// Run cleanup on page load and periodically
jQuery(document).ready(function() {
  cleanupBrokenDatepickers();
  
  // Clean up periodically to catch any new broken instances
  setInterval(cleanupBrokenDatepickers, 5000);
});

// Handle datepicker close events to restore body scroll
jQuery(document).on('click', function(e) {
  try {
    // Ensure e.target exists before proceeding
    if (!e || !e.target) {
      return;
    }
    
    // Check if click is outside datepicker and date fields
    var $target = jQuery(e.target);
    var isInsideDatepicker = $target.closest('.ui-datepicker').length > 0;
    var isInsideDateField = $target.closest('#field_q_rental_from_date, #field_q_rental_to_date').length > 0;
    var isDatepickerElement = $target.hasClass('ui-datepicker') || $target.hasClass('ui-datepicker-header') || 
                              $target.hasClass('ui-datepicker-prev') || $target.hasClass('ui-datepicker-next') ||
                              $target.hasClass('ui-datepicker-month') || $target.hasClass('ui-datepicker-year') ||
                              $target.hasClass('ui-datepicker-calendar') || $target.closest('.ui-datepicker-calendar').length > 0;
    
    // Only close if click is completely outside datepicker and date fields
    if (!isInsideDatepicker && !isInsideDateField && !isDatepickerElement) {
      // Close any open datepickers
      jQuery('#field_q_rental_from_date, #field_q_rental_to_date').datepicker('hide');
      
      // Restore body scroll on mobile
      if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768) {
        jQuery('body').removeClass('datepicker-open');
      }
    }
  } catch (error) {
    // Log error but don't break the page
    console.warn('Error in datepicker close handler:', error);
  }
});

// Additional touch event handling for mobile datepicker
jQuery(document).on('touchstart', '.ui-datepicker', function(e) {
  // Only stop propagation to prevent popup closure
  if (e && typeof e.stopPropagation === 'function') {
    e.stopPropagation();
  }
  // Don't prevent default - let datepicker handle its own events
});

// Handle orientation change on mobile
jQuery(window).on('orientationchange resize', function() {
  // Close any open datepickers on orientation change
  jQuery('#field_q_rental_from_date, #field_q_rental_to_date').datepicker('hide');
  
  // Restore body scroll
  jQuery('body').removeClass('datepicker-open');
  
  // Small delay to ensure proper cleanup
  setTimeout(function() {
    jQuery('body').removeClass('datepicker-open');
  }, 100);
});



// Postcode fields: enforce 6 numeric digits (works for dynamically added fields)
(function () {
  // Updated selectors to include both old format and Elementor form format
  var selectors = '#field_c_postcode, #field_q_post_code, input[name*="postcode"], input[name*="post_code"]';

  function sanitizeToSixDigits(value) {
    return String(value || '').replace(/\D/g, '').slice(0, 6);
  }

  function applyPostcodeAttributes(el) {
    if (!el) return;
    el.setAttribute('inputmode', 'numeric');
    el.setAttribute('pattern', '\\d{6}');
    el.setAttribute('min', '0');
    el.setAttribute('max', '999999');
  }

  function validatePostalCode(value) {
    return /^\d{6}$/.test(value);
  }

  function showValidationMessage(el, isValid) {
    var fieldGroup = el.closest('.elementor-field-group, .form-field');
    if (!fieldGroup) return;
    
    var existingMsg = fieldGroup.querySelector('.postal-validation-msg');
    if (existingMsg) {
      existingMsg.remove();
    }
    
    if (!isValid && el.value.length > 0) {
      var msg = document.createElement('div');
      msg.className = 'postal-validation-msg';
      msg.style.cssText = 'color: rgb(220, 53, 69);font-size: 11px;margin-top: 5px;position: absolute;bottom: -15px;';
      msg.textContent = 'Postal code must be exactly 6 digits';
      fieldGroup.appendChild(msg);
    }
  }

  // Initial pass on DOM ready
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll(selectors).forEach(function (el) {
      applyPostcodeAttributes(el);
      var cleaned = sanitizeToSixDigits(el.value);
      if (el.value !== cleaned) el.value = cleaned;
      showValidationMessage(el, validatePostalCode(el.value));
    });
  });

  // Delegate input events so it also works when fields are injected later
  document.addEventListener(
    'input',
    function (event) {
      var target = event.target;
      if (!(target instanceof Element)) return;
      if (!target.matches(selectors)) return;

      applyPostcodeAttributes(target);
      var cleaned = sanitizeToSixDigits(target.value);
      if (target.value !== cleaned) {
        target.value = cleaned;
      }
      showValidationMessage(target, validatePostalCode(target.value));
    },
    true
  );

  // Also listen for blur events to validate on field exit
  document.addEventListener(
    'blur',
    function (event) {
      var target = event.target;
      if (!(target instanceof Element)) return;
      if (!target.matches(selectors)) return;
      
      showValidationMessage(target, validatePostalCode(target.value));
    },
    true
  );
})();
