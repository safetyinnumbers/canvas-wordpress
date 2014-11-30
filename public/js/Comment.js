(function(exports, undefined) {
  var $ = jQuery;

  function getActionKey(key) {
    return 'CommentMetaStore:' + key;
  }

  var CommentMetaStore = {
    inited: false, 
    userToken: window.localStorage[getActionKey('userToken')] || null,

    init: function(callback) {
      if (this.inited) {
        return;
      }

      this.inited = true;

      if (!this.userToken) {
        $.ajax({
          dataType: "jsonp",
          url: CanvasConstants.tokenURL,
          success: function(data) {
            console.log(data);
            this.userToken = data.user_token;
            window.localStorage[getActionKey('userToken')] = this.userToken;
            callback(this.userToken);
          }
        })
      } else {
        callback(this.userToken)
      }
    },

    getUserToken: function() {
      return this.userToken;
    },

    getActions: function(commentId) {
      var raw = window.localStorage[getActionKey(commentId)];
      return (raw) ? JSON.parse(raw) : {};
    },

    putLocalAction: function(commentId, action, value) {
      var actions = CommentMetaStore.getActions(commentId);
      actions[action] = value;
      window.localStorage[getActionKey(commentId)] = JSON.stringify(actions);
    },

    putAction: function(commentId, action, value) {
      $.ajax({
        type: "post",
        dataType: "json",
        url: CanvasConstants.ajaxURL,
        data: {
          action: "canvas_" + action, 
          userToken: this.userToken,
          id: commentId,
          type: value.type,
          details: value.details
        }
      });

      this.putLocalAction(commentId, action, value);
    }
  };

  var CommentActions = {
    upvote: function(commentId) {
      CommentMetaStore.putAction(commentId, 'vote', {type: 'up'});
    },

    downvote: function(commentId) {
      CommentMetaStore.putAction(commentId, 'vote', {type: 'down'});
    },

    flag: function(commentId, type, details) {
      console.log(arguments);

      CommentMetaStore.putAction(commentId, 'flag', {
        type: type,
        details: details
      });
    }
  };

  var FLAGS = {
    '1': 'This comment is spam',
    '2': 'This comment is abusive',
  };

  var FLAG_OTHER = '3';

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
              '<input type="radio" class="comment-option-other" data-flag="' + FLAG_OTHER + '" name="flags-' + id + '"/>' +
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

      if (flagId === FLAG_OTHER) {
        otherDetails.show();
      } else {
        otherDetails.hide();
        onSubmit(flagId);
      }
    });
    flag.find('.comment-other-submit').click(function(e) {
      e.preventDefault();
      onSubmit(FLAG_OTHER, flag.find('.comment-other-description').val());
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
    var actions = CommentMetaStore.getActions(this.id);

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

  function CommentForm(node) {
    node.prepend($(
      '<input type="hidden" name="userToken" value="'+
        CommentMetaStore.userToken + 
      '"/>'
    ));
  }

  CommentMetaStore.init(function (userToken) {
    $('.comment').each(function(idx, comment) {
      new Comment($(comment));
    });

    new CommentForm($('#commentform'));
  });
})(window);