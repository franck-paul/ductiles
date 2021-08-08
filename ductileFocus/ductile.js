/*global $ */
'use strict';

$(function () {
  const create_name = (text) => {
    return (
      text
        // Convert text to lower case.
        .toLowerCase()

        // Remove leading and trailing spaces, and any non-alphanumeric
        // characters except for ampersands, spaces and dashes.
        .replace(/^\s+|\s+$|[^a-z0-9&\s-]/g, '')

        // Replace '&' with 'and'.
        .replace(/&/g, 'and')

        // Replaces spaces with dashes.
        .replace(/\s/g, '-')

        // Squash any duplicate dashes.
        .replace(/(-)+\1/g, '$1')
    );
  };

  const add_link = function () {
    // Convert the h2 element text into a value that
    // is safe to use in a name attribute.
    const name = create_name($(this).text());

    // Create a name attribute in the following sibling
    // to act as a fragment anchor.
    $(this).next().attr('name', name);

    // Replace the h2.toggle element with a link to the
    // fragment anchor.  Use the h2 text to create the
    // link title attribute.
    $(this).html('<a href="#' + name + '" title="Reveal ' + $(this).text() + ' content">' + $(this).html() + '</a>');
  };

  const toggle = function (event) {
    event.preventDefault();

    // Toggle the 'expanded' class of the h2.toggle
    // element, then apply the slideToggle effect
    // to all siblings.
    $(this).toggleClass('expanded').nextAll().slideToggle('fast');
  };

  const remove_focus = function () {
    // Use the blur() method to remove focus.
    $(this).blur();
  };

  $(document).ready(function () {
    if ($(window).width() < 1024) {
      // Set toggle class to each #sidebar h2
      $('#sidebar div div h2').addClass('toggle');

      // Hide all h2.toggle siblings
      $('#sidebar div div h2').nextAll().hide();

      // Add a link to each h2.toggle element.
      $('h2.toggle').each(add_link);

      // Add a click event handler to all h2.toggle elements.
      $('h2.toggle').on('click', toggle);

      // Remove the focus from the link tag when accessed with a mouse.
      $('h2.toggle a').on('mouseup', remove_focus);
    }
  });
});
