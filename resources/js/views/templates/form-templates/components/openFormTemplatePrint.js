import { FORM_PAGE_SIZE } from '../constants';

/**
 * Open print preview in a new window sized for A5 landscape (half letter landscape).
 */
export function openFormTemplatePrintPreview(html, title = 'Form template') {
  const body = (html || '').trim() ? html : '<p><br></p>';
  const w = window.open('', '_blank', 'noopener,noreferrer');
  if (!w) {
    return false;
  }
  const { widthMm, heightMm, cssPageSize, printMargins, contentPadding, previewFontSize, previewLineHeight } =
    FORM_PAGE_SIZE;
  const doc = [
    '<!DOCTYPE html><html><head><meta charset="utf-8">',
    `<title>${title}</title>`,
    '<style>',
    `@page { size: ${cssPageSize}; margin: ${printMargins}; }`,
    'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;margin:0;padding:0;color:#303133;}',
    `.form-sheet{width:${widthMm}mm;min-height:${heightMm}mm;max-width:100%;margin:0 auto;padding:${contentPadding};`,
    `box-sizing:border-box;font-size:${previewFontSize};line-height:${previewLineHeight};}`,
    'table{border-collapse:collapse;width:100%;margin:.35em 0;}',
    'td,th{border:1px solid #ccc;padding:6px;vertical-align:top;font-size:inherit;}',
    'th{background:#f5f7fa;font-weight:600;}',
    'img{max-width:100%;height:auto;}',
    'hr{border:none;border-top:1px solid #dcdfe6;margin:10px 0;}',
    '</style></head><body><div class="form-sheet">',
    body,
    '</div></body></html>',
  ].join('');
  w.document.open();
  w.document.write(doc);
  w.document.close();
  setTimeout(() => {
    try {
      w.focus();
      w.print();
    } catch (e) {
      /* ignore */
    }
  }, 350);
  return true;
}
