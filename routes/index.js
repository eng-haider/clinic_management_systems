const express = require('express');
const router = express.Router();
const { checkRoleAccess } = require('../middleware/roleMiddleware');

// Restrict access to other sections for role 6
router.use('/patients', checkRoleAccess(['patients']));
router.use('/appointments', checkRoleAccess(['appointments']));
router.use('/doctors', checkRoleAccess(['doctors']));
// Bills route allows role 6 access
router.use('/bills', require('./bills'));

module.exports = router;
