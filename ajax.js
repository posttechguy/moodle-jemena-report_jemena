/**
 * Listens for when a course has been selected or changed
 *
 * Makes an ajax call to return the list of course
 * prerequisites for a given course
 *
 * @author Mark Nelson - Pukunui Technology
 * @copyright Jemena
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

YUI().use("json", "io-base", "node-base", function(Y) {
    // Change the available courses
    change_course = function(e) {
        var processarea = Y.one("#id_category").get("value");
        var role = Y.one("#id_role").get("value");
        Y.io("ajax.php", {
            method: "POST",
            data: "category="+processarea+"&role="+role,
            on: {
                success: function (id, result) {
                    // Fill the table
                    var node = Y.one("#id_course");
                    var html = Y.JSON.parse(result.responseText);
                    node.setContent(html);
                    node.set('value', 0);
                },
                failure: function (id, result) {
                    // Do nothing
                }
            }
        });
    }
    // When the process area or role is changed
    Y.on("change", change_course, "#id_category");
    Y.on("change", change_course, "#id_role");
});
