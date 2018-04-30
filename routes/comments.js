var express = require('express');
var router = express.Router({mergeParams: true});
var Campground = require('../models/campground');
var Comment = require('../models/comment');
var middleware = require('../middleware');

// New comment
router.get('/new', middleware.isLoggedIn, function(req, res){
    Campground.findById(req.params.id, function(err, foundCampground){
        if(err){
            console.log(err);
        }
        else{
            res.render('comments/new', {campground: foundCampground});
        }
    });
});

router.post('/', middleware.isLoggedIn, function(req, res){
    Campground.findById(req.params.id, function(err, campground){
        if(err){
            console.log(err);
            req.flash('error', 'Comment not found');
            res.redirect('/campgrounds');
        }
        else{
            Comment.create(req.body.comment, function(err, newComment){
                if(err){
                    console.log(err);
                    req.flash('error', 'Something went wrong');
                }
                else{
                    newComment.author.id = req.user._id;
                    newComment.author.username = req.user.username;
                    // console.log(newComment);
                    newComment.save();
                    campground.comments.push(newComment);
                    campground.save();
                    req.flash('success', 'Successfully added comment');
                    res.redirect('/campgrounds/' + campground._id);
                }
            });
        }
    });
});

// Edit comment
router.get('/:commentId/edit', middleware.checkCommentOwnership, function(req, res){
    Comment.findById(req.params.commentId, function(err, foundComment){
        if(err){
            req.flash('error', 'Comment not found');
            res.redirect('back');
        }
        else{
            res.render('comments/edit', {campground_id: req.params.id, comment: foundComment});
        }
    });
});

// Update comment
router.put('/:commentId', middleware.checkCommentOwnership, function(req, res){
    Comment.findByIdAndUpdate(req.params.commentId, req.body.comment, function(err, updatedComment){
        if(err){
            req.flash('error', 'Comment not found');
            res.redirect('back');
        }
        else{
            req.flash('success', 'Successfully updated comment');
            res.redirect('/campgrounds/'+req.params.id);
        }
    });
});

// Delete comment
router.delete('/:commentId', middleware.checkCommentOwnership, function(req, res) {
    Comment.findByIdAndRemove(req.params.commentId, function(err, foundComment){
        if(err){
            req.flash('error', 'Comment not found');
            res.redirect("back");
        }
        else{
            req.flash('success', 'Successfully deleted comment');
            res.redirect("/campgrounds/" + req.params.id);
        }
    });
});

module.exports = router;