var express = require('express');
var router = express.Router({mergeParams: true});
var Campground = require('../models/campground');
var middleware = require('../middleware');

// All campgrounds
router.get('/', function(req, res){
    Campground.find({}, function(err, allCampgrounds){
        if(err){
            console.log(err);
        }
        else{
            res.render('campgrounds/index', {campgrounds:allCampgrounds});
        }
    });
});

// New campground
router.get('/new', middleware.isLoggedIn, function(req, res){
    res.render('campgrounds/new');
});

router.post('/', middleware.isLoggedIn, function(req, res){
    var name = req.body.name;
    var price = req.body.price;
    var image = req.body.image;
    var description = req.body.description;
    var author = {
        id: req.user._id,
        username: req.user.username
    };
    var newCampground = {name:name, price:price, image:image, description: description, author: author};
    Campground.create(newCampground, function(err, newlyCreated){
        if(err){
            console.log(err);
        }
        else{
            req.flash('success', 'Successfully added campground');
            res.redirect('/campgrounds');
        }
    });
});

// Show campgrounds
router.get('/:id', function(req, res){
    Campground.findById(req.params.id).populate('comments').exec(function(err, foundCampground){
        if(err){
            req.flash('error', 'Campground not found');
            console.log(err);
        }
        else{
            res.render('campgrounds/show', {campground: foundCampground});
        }
    });
    
});

// Edit campground
router.get('/:id/edit', middleware.checkOwnership, function(req, res) {
    Campground.findById(req.params.id, function(err, foundCampground){
        if(err){
            req.flash('error', 'Campground not found');
            res.redirect('back');
        }
        res.render('campgrounds/edit', {campground: foundCampground});
    })
});

// Update campground
router.put('/:id', middleware.checkOwnership, function(req, res) {
    Campground.findByIdAndUpdate(req.params.id, req.body.campground, function(err, updatedCampground){
        if(err){
            req.flash('error', 'Campground not found');
            res.redirect('/campgrounds');
        }
        else{
            req.flash('success', 'Successfully updated campground');
            res.redirect('/campgrounds/' + req.params.id);
        }
    });
});

// Delete campground
router.delete('/:id/', middleware.checkOwnership, function(req, res) {
    Campground.findByIdAndRemove(req.params.id, function(err, foundBlog){
        if(err){
            req.flash('error', 'Campground not found');
            res.redirect("/campgrounds");
        }
        else{
            req.flash('success', 'Successfully deleted campground');
            res.redirect("/campgrounds");
        }
    });
});

module.exports = router;