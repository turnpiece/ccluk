import Fetcher from '../utils/fetcher';

const MinificationScanner = ( totalSteps, currentStep ) => {

    totalSteps = parseInt( totalSteps );
    currentStep = parseInt( currentStep );
    let cancelling = false;

    let obj = {
        scan: function() {
            let remainingSteps = totalSteps - currentStep;
            if ( currentStep !== 0 ) {
                // Scan started on a previous pageload
                step( remainingSteps );
            }
            else {
                Fetcher.minification.startCheck()
                    .then( () => {
                        step( remainingSteps );
                    });
            }

        },
        cancel: function() {
            cancelling = true;
            return Fetcher.minification.cancelScan();
        },
        getProgress: function() {
            if ( cancelling ) {
                return 0;
            }
            const remainingSteps = totalSteps - currentStep;
            return Math.min( Math.round( ( parseInt( ( totalSteps - remainingSteps ) ) * 100 ) / totalSteps ), 99 );
        },
        // Overridable functions
        onFinishStep: function( progress ) {},
        onFinish: function( response ) {},
    };

    /**
     * Execute a scan step recursively
     *
     * Private to avoid overrdings
     *
     * @param remainingSteps
     */
    const step = function( remainingSteps ) {
        if ( remainingSteps >= 0 ) {
            currentStep = totalSteps - remainingSteps;
            Fetcher.minification.checkStep( currentStep )
                .then( () => {
                    remainingSteps = remainingSteps - 1;
                    obj.onFinishStep( obj.getProgress() );
                    step( remainingSteps );
                });
        }
        else {
            Fetcher.minification
                .finishCheck()
                .then( obj.onFinish );
        }
    };

    return obj;
};

export default MinificationScanner;