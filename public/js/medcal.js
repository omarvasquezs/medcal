/**
 * Combined JavaScript for MedCal plugin
 * Merges functionality from medcal-public.js and medcal-tabs.js
 */
(function($) {
    'use strict';

    /**
     * Initialize the calculators when the DOM is fully loaded
     */
    function initializeAllCalculators() {
        $('.calculator').each(function() {
            initializeCalculator($(this).attr('id'));
        });
    }
    
    /**
     * Initialize a specific calculator by its ID
     */
    function initializeCalculator(calculatorId) {
        const $calculator = $('#' + calculatorId);
        if (!$calculator.length) return;
        
        const $range = $calculator.find('input[type="range"]');
        const $total = $calculator.find('[id$="-total"]');
        const $term = $calculator.find('[id$="-term"]');
        
        if (!$range.length || !$total.length || !$term.length) return;
        
        // Initialize with default values
        updateCalculation($calculator, $range.val());
        
        // Add event listener for range input
        $range.on('input', function() {
            updateCalculation($calculator, $(this).val());
        });
        
        // Add event listeners for arrows
        $calculator.find('.arrow.left').on('click', function() {
            const min = parseInt($range.attr('min'));
            const current = parseInt($range.val());
            if (current > min) {
                $range.val(current - 1);
                updateCalculation($calculator, current - 1);
            }
        });
        
        $calculator.find('.arrow.right').on('click', function() {
            const max = parseInt($range.attr('max'));
            const current = parseInt($range.val());
            if (current < max) {
                $range.val(current + 1);
                updateCalculation($calculator, current + 1);
            }
        });
    }
    
    /**
     * Update the calculation display based on the selected term
     */
    function updateCalculation($calculator, term) {
        const $total = $calculator.find('[id$="-total"]');
        const $term = $calculator.find('[id$="-term"]');
        
        // Set the term display (singular/plural form)
        $term.text(`${term} ${parseInt(term) === 1 ? 'CUOTA' : 'CUOTAS'}`);
        
        // Calculate and display the monthly payment
        const totalCost = parseFloat($total.data('total-cost'));
        const monthlyPayment = (totalCost / term).toFixed(2);
        
        $total.text(new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(monthlyPayment));
    }

    /**
     * Initialize the tab functionality
     */
    function initTabs() {
        // Tab click handling
        $('.medcal-tabbed-container .nav-link').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).data('bs-target');
            
            // Remove active class from all tabs and tab panes
            $(this).closest('.nav-tabs').find('.nav-link').removeClass('active').attr('aria-selected', 'false');
            $(this).closest('.medcal-tabbed-container').find('.tab-pane').removeClass('show active');
            
            // Add active class to current tab and tab pane
            $(this).addClass('active').attr('aria-selected', 'true');
            $(target).addClass('show active');
            
            // Initialize the calculator in the newly active tab
            const calculatorId = $(target).find('.calculator').attr('id');
            if (calculatorId) {
                // This will trigger a recalculation for the newly visible calculator
                const rangeInput = $('#' + calculatorId).find('input[type="range"]');
                if (rangeInput.length) {
                    rangeInput.trigger('input');
                }
            }
        });
    }

    $(document).ready(function() {
        // Initialize all calculators
        initializeAllCalculators();
        
        // Initialize tabs
        initTabs();
    });

})(jQuery);