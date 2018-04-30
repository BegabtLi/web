var express = require('express');
var router = express.Router({mergeParams: true});
var passport = require('passport');
var User = require('../models/user');

// Home page
router.get('/', function(req, res){
    // res.send('<h1>Welcome to the Home Page!</h1>');
    res.render('landing');
});

// Register
router.get('/register', function(req, res) {
    res.render('register');
});

router.post('/register', function(req, res) {
    var newUser = new User({username: req.body.username});
    User.register(newUser, req.body.password, function(err, user){
        if(err){
            req.flash('error', err.message);
            return res.render('register');
        }
        else{
            passport.authenticate('local')(req, res, function(){
                req.flash('success', 'Welcome to YelpCamp, '+user.username);
                res.redirect('/campgrounds');
            });
        }
    });
});


// Login
router.get('/login', function(req, res) {
    res.render('login');
});

router.post('/login', passport.authenticate('local', {
    // successRedirect: '/campgrounds',
    failureRedirect: '/login',
    failureFlash: 'Invalid username or password'
}), function(req, res) {
    req.flash('success', 'Welcome back, '+ req.body.username);
    res.redirect('/campgrounds');
});

// Logout
router.get('/logout', function(req, res) {
    req.logout();
    req.flash('success', 'Logged you out.');
    res.redirect('/campgrounds');
});

module.exports = router;