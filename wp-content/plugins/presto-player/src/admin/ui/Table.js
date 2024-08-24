const { __ } = wp.i18n;
const { useRef, useEffect, useState } = wp.element;
const { Card, CardBody, Flex, FlexBlock, Button, ButtonGroup } = wp.components;

import classNames from "classnames";

export default ({ columns, data, onRowClick, title }) => {
  if (!data.length) {
    return (
      <Card size="large" className="presto-card table-card">
        <CardBody className="presto-flow">
          <div className="presto-card__title">{title}</div>
          <div style={{ opacity: 0.65 }}>
            {__("No data available.", "presto-player")}
          </div>
        </CardBody>
      </Card>
    );
  }

  return (
    <Card size="large" className="presto-card table-card">
      <CardBody className="presto-flow">
        <div className="presto-card__title">{title}</div>
        <table
          role="table"
          className={classNames("presto-table", { "is-clickable": onRowClick })}
        >
          <thead role="rowgroup">
            <tr role="row">
              {columns &&
                columns.map((column) => {
                  return (
                    <th key={column.key} role="columnheader">
                      {column.label}
                    </th>
                  );
                })}
            </tr>
          </thead>

          <tbody role="rowgroup">
            {data.map((row, rowIndex) => {
              return (
                <tr
                  role="row"
                  key={`row-${rowIndex}`}
                  onClick={() => onRowClick && onRowClick(row)}
                >
                  {columns.map((column, columnIndex) => {
                    return (
                      <td
                        role="cell"
                        data-title={column.label}
                        key={`row-${rowIndex}-${columnIndex}`}
                        aria-label={column.label}
                      >
                        {column.render ? (
                          column.render(row)
                        ) : (
                          <div>{column.value(row)}</div>
                        )}
                      </td>
                    );
                  })}
                </tr>
              );
            })}
          </tbody>
        </table>
      </CardBody>
    </Card>
  );
};
