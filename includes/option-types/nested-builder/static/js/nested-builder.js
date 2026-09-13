/**
 * Nested Builder — per-instance builder tweaks.
 *
 * The base `isValidDirectParent(type, parentType)` hardcodes the page-builder's own
 * types (section-like / flexbox / container / column) and otherwise only accepts a
 * parent of `column` or `flexbox`. Our custom items (pane / box / text) are unknown to
 * it, so a drop into a Box/Pane is judged "invalid" and scaffolded to root — the
 * lost-nesting symptom. We override it for OUR builder instances only (never the page
 * builder) to use the generic, per-item contract: a parent accepts a child when the
 * parent's allowIncomingType(child) is true, and the child accepts the destination when
 * its allowDestinationType(parent) is true. Both are declared on the item classes.
 */
fwEvents.on('fw-builder:' + 'nested-builder' + ':register-items', function (builder) {
	builder.isValidDirectParent = function (type, parentType) {
		var root = (parentType === null || typeof parentType === 'undefined');

		var Incoming = this.getRegisteredItemClassByType(type);
		var childAllowsDest = (Incoming && Incoming.prototype.allowDestinationType)
			? Incoming.prototype.allowDestinationType(root ? null : parentType)
			: true;
		if (!childAllowsDest) { return false; }

		if (root) { return true; }

		var Parent = this.getRegisteredItemClassByType(parentType);
		return !!(Parent && Parent.prototype.allowIncomingType && Parent.prototype.allowIncomingType(type));
	};
});
