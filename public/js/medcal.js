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
        
        // Get the term step configuration
        const termStep = parseInt($range.data('term-step')) || 3;
        const minTerm = parseInt($range.attr('min'));
        const maxTerm = parseInt($range.attr('max'));
        
        // Calculate valid terms based on the step
        const validTerms = calculateValidTerms(minTerm, maxTerm, termStep);
        
        // Set the range step to 1 (we'll handle the valid terms in the update logic)
        $range.attr('step', 1);
        
        // Find the nearest valid term for the default value
        const defaultValue = parseInt($range.val());
        const nearestValidTerm = findNearestValidTerm(defaultValue, validTerms);
        $range.val(nearestValidTerm);
        
        // Initialize with default values
        updateCalculationWithSteps($calculator, nearestValidTerm, validTerms);
        
        // Add event listener for range input
        $range.on('input', function() {
            const currentValue = parseInt($(this).val());
            const nearestValidTerm = findNearestValidTerm(currentValue, validTerms);
            
            if (currentValue !== nearestValidTerm) {
                $(this).val(nearestValidTerm);
            }
            
            updateCalculationWithSteps($calculator, nearestValidTerm, validTerms);
        });
        
        // Add event listeners for arrows
        $calculator.find('.arrow.left').on('click', function() {
            const currentValue = parseInt($range.val());
            const currentIndex = validTerms.indexOf(currentValue);
            
            if (currentIndex > 0) {
                const newValue = validTerms[currentIndex - 1];
                $range.val(newValue);
                updateCalculationWithSteps($calculator, newValue, validTerms);
            }
        });
        
        $calculator.find('.arrow.right').on('click', function() {
            const currentValue = parseInt($range.val());
            const currentIndex = validTerms.indexOf(currentValue);
            
            if (currentIndex < validTerms.length - 1) {
                const newValue = validTerms[currentIndex + 1];
                $range.val(newValue);
                updateCalculationWithSteps($calculator, newValue, validTerms);
            }
        });
    }
    
    /**
     * Calculate valid terms based on min, max and step values
     */
    function calculateValidTerms(min, max, step) {
        // Always include min as the first term (which could be 1 or another value)
        let validTerms = [min];
        
        // Then add terms that are multiples of the step until we reach max
        // Start from the first multiple of step that is greater than min
        let firstStep = Math.ceil(min / step) * step;
        if (firstStep === min) {
            firstStep += step;
        }
        
        for (let i = firstStep; i <= max; i += step) {
            validTerms.push(i);
        }
        
        // Make sure max is included if it's not already
        if (validTerms[validTerms.length - 1] !== max && max > step) {
            validTerms.push(max);
        }
        
        // Remove duplicates and sort
        return [...new Set(validTerms)].sort((a, b) => a - b);
    }
    
    /**
     * Find the nearest valid term to a given value
     */
    function findNearestValidTerm(value, validTerms) {
        if (validTerms.includes(value)) {
            return value;
        }
        
        // Find the nearest valid term by distance
        let nearest = validTerms[0];
        let minDistance = Math.abs(value - nearest);
        
        for (let i = 1; i < validTerms.length; i++) {
            const distance = Math.abs(value - validTerms[i]);
            if (distance < minDistance) {
                minDistance = distance;
                nearest = validTerms[i];
            }
        }
        
        return nearest;
    }
    
    /**
     * Update the calculation display based on the selected term and valid terms list
     */
    function updateCalculationWithSteps($calculator, term, validTerms) {
        const $total = $calculator.find('[id$="-total"]');
        const $term = $calculator.find('[id$="-term"]');
        const $range = $calculator.find('input[type="range"]');
        
        // Update the range position if needed
        if (parseInt($range.val()) !== term) {
            $range.val(term);
        }
        
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
     * Legacy function for backward compatibility
     */
    function updateCalculation($calculator, term) {
        const $total = $calculator.find('[id$="-total"]');
        const $term = $calculator.find('[id$="-term"]');
        const $range = $calculator.find('input[type="range"]');
        
        // Get term step or use default
        const termStep = parseInt($range.data('term-step')) || 3;
        const minTerm = parseInt($range.attr('min'));
        const maxTerm = parseInt($range.attr('max'));
        
        // Calculate valid terms
        const validTerms = calculateValidTerms(minTerm, maxTerm, termStep);
        
        // Find the nearest valid term
        const nearestValidTerm = findNearestValidTerm(parseInt(term), validTerms);
        
        // Use the new function with the valid terms
        updateCalculationWithSteps($calculator, nearestValidTerm, validTerms);
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