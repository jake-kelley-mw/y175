import React from "react";
/**
 * Name exporting instead of default export for supporting multiple export from the same JS file.
 */

/**
 * Render prop value. Some attribute values need to be parsed before can be displayed
 *
 */
export function _renderProp(value, fieldType) {
  const utils = window.ET_Builder.API.Utils;
  let output = "";

  if (!value) {
    return output;
  }

  switch (fieldType) {
    case "select_icons":
      output = (
        <i className="et-tmdivi-icon">
          {utils.processFontIcon(value)}
        </i>
      );
      break;
    default:
      output = (
        <strong>unsupported fieldType passed to _renderProp function</strong>
      );
  }

  return output;
}
