(function(exports, undefined) {
  var $ = jQuery;

  function getActionKey(key) {
    return 'CommentActionStore:' + key;
  }

  var CommentActionStore = {
    // TODO: fetch these sorts of details from the WordPress site.

    get: function(commentId) {
      var raw = window.localStorage[getActionKey(commentId)];
      return (raw) ? JSON.parse(raw) : {};
    },

    putLocalAction: function(commentId, action, value) {
      var actions = CommentActionStore.get(commentId);
      actions[action] = value;
      window.localStorage[getActionKey(commentId)] = JSON.stringify(actions);
    }
  };

  function sendAction(action, details) {
    // TODO: fetch the user token.

    $.ajax({
      type: "post",
      dataType: "json",
      url: CanvasConstants.ajaxURL,
      data: $.merge({action: action, userToken: null}, details)
    });
  }

  var CommentActions = {
    upvote: function(commentId) {
      CommentActionStore.putLocalAction(commentId, 'vote', 'up');
      sendAction('canvas_vote', {type: 'up'});
    },

    downvote: function(commentId) {
      CommentActionStore.putLocalAction(commentId, 'vote', 'down');
      sendAction('canvas_vote', {type: 'down'});
    },

    flag: function(commentId, type, details) {
      CommentActionStore.putLocalAction(commentId, 'flag', {
        type: type,
        details: details
      });
      sendAction('canvas_flag', {type: 'type', details: 'details'});
    }
  };

  var FLAGS = {
    'spam': 'This comment is spam',
    'abuse': 'This comment is abusive',
  };

  function CommentFlag(id, onCancel, onSubmit) {
    var flag = $(
      '<div class="comment-flag">'+
        '<h3>' +
          'Flag Comment' +
          '<small><a class="cancel-comment-flag-link" href="#">Cancel flag</a></small>' +
        '</h3>' +
        '<form class="comment-flag-form">' +
          '<div class="comment-options">' +
            '<p><label>' +
              '<input type="radio" class="comment-option-other" data-flag="other" name="flags-' + id + '"/>' +
              '<i>Other</i>' +
            '</label></p>' +
          '</div>' +
          '<p class="comment-other" style="display: none">' +
            '<textarea ' +
              'class="comment-other-description" '+
              'placeholder="Describe the abuse in more detail...">'+
            '</textarea>' +
            '<input class="comment-other-submit" type="submit" value="Submit"/>' +
          '</p>' +
        '</form>' +
      '</div>'
    );

    var options = flag.find('.comment-options');
    var otherDetails = flag.find('.comment-other');

    for (var flagId in FLAGS) {
      var option = $(
        '<p><label>' +
          '<input type="radio" data-flag="' + flagId + '" name="flags-' + id + '"/>' +
          '<i>' + FLAGS[flagId] +  '</i>' +
        '</label></p>'
      );

      options.prepend(option);
    }

    flag.find('.cancel-comment-flag-link').click(onCancel);
    flag.find('input[type=radio]').change(function(node) {
      var flagId = $(this).attr('data-flag');

      if (flagId === 'other') {
        otherDetails.show();
      } else {
        otherDetails.hide();
        onSubmit(flagId);
      }
    });
    flag.find('.comment-other-submit').click(function(e) {
      e.preventDefault();
      onSubmit('other', flag.find('.comment-other-description').val());
    });

    return flag;
  }

  function CommentFlagComplete() {
    return $('<p>Your flag has been submitted to the moderators.</p>');
  }

  function Comment(node) {
    this.id = node.attr('id').replace('comment-', '');
    this.commentNode = node.find('.comment-body');
    this.flagNode = null;
    this.render();
  }    

  Comment.prototype.handleFlag = function(event) {
    event.preventDefault();
    this.flagNode = new CommentFlag(
      this.id, 
      function() { this.flagNode.replaceWith(this.commentNode); }.bind(this),
      this.handleCompleteFlag.bind(this)
    );
    this.commentNode.replaceWith(this.flagNode);
  };

  Comment.prototype.handleCompleteFlag = function(type, details) {
    CommentActions.flag(this.id, type, details);
    this.flagNode.replaceWith(new CommentFlagComplete());
  };

  Comment.prototype.handleUpvote = function(event) {
    event.preventDefault();
    CommentActions.upvote(this.id);
    this.updateActions();
  };

  Comment.prototype.handleDownvote = function(event) {
    event.preventDefault();
    CommentActions.downvote(this.id);
    this.updateActions();
  };

  Comment.prototype.updateActions = function() {
    var actions = CommentActionStore.get(this.id);

    for (var action in actions) {
      switch (action) {
        case 'vote':
          this.votingButtons.replaceWith(
            $('<span class="comment-voting-done">Voted</span>')
          );
          break;
        case 'flag':
          this.flagButton.replaceWith(
            $('<span class="comment-flag-done">Flagged</span>')
          );
          break;
      }
    }
  };

  Comment.prototype.render = function() {
    var upvote = $('<a class="comment-upvote-button" href="#">Upvote</a>')
      .click(this.handleUpvote.bind(this));
    var downvote = $('<a class="comment-downvote-button" href="#">Downvote</a>')
      .click(this.handleDownvote.bind(this));

    this.votingButtons = $('<div class="comment-voting"></div>')
      .append(upvote, downvote);

    this.flagButton = $('<a class="comment-flag-button" href="#">Flag</a>')
      .click(this.handleFlag.bind(this));

    var moderation = $('<div class="comment-moderate"></div>');
    moderation.append(this.votingButtons, this.flagButton);

    this.updateActions();
    this.commentNode.find('.reply').append(moderation);
  };

  $('.comment').each(function(idx, comment) {
    new Comment($(comment));
  });
  
})(window);