export default {
    debug: true,
    state: {
        summary: security_tweaks.summary,
        issuesRules: security_tweaks.issues,
        ignoreRules: security_tweaks.ignored,
        fixedRules: security_tweaks.fixed
    },
    update(data) {
        if (data.success === false) {
            return;
        }
        if (data.data.reload !== undefined) {
            return;
        }
        this.state.summary.fixed_count = data.data.summary.fixed;
        this.state.summary.issues_count = data.data.summary.issues;
        this.state.summary.ignore_count = data.data.summary.ignore;
        this.state.issuesRules = data.data.issues;
        this.state.fixedRules = data.data.fixed;
        this.state.ignoreRules = data.data.ignore;
        setTimeout(function () {
            jQuery('.sui-accordion').each(function () {
                SUI.suiAccordion(this);
            });
        }, 500)
    },
}