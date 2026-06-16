import { FORM_TEMPLATE_CLINIC_LOGO } from '../constants';

export function clinicHeaderSnippetHtml(logoUrl = FORM_TEMPLATE_CLINIC_LOGO) {
  const logo = logoUrl || FORM_TEMPLATE_CLINIC_LOGO;
  return [
    '<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">',
    '<tr>',
    `<td style="width:72px;vertical-align:top;border:none;padding:0 8px 0 0;">`,
    `<img src="${logo}" alt="Clinic logo" style="max-width:64px;height:auto;" />`,
    '</td>',
    '<td style="vertical-align:top;border:none;padding:0;">',
    '<p style="margin:0 0 4px;font-size:18px;font-weight:700;">{{doctor_name}}</p>',
    '<p style="margin:0 0 2px;font-size:12px;color:#606266;">Clinic / hospital letterhead</p>',
    '<p style="margin:0;font-size:11px;color:#909399;">Edit this block after inserting</p>',
    '</td>',
    '</tr>',
    '</table>',
    '<hr>',
  ].join('');
}

export function signatureBlockSnippetHtml() {
  return [
    '<p><br></p>',
    '<table style="width:100%;border-collapse:collapse;border:none;">',
    '<tr>',
    '<td style="width:55%;vertical-align:top;border:none;padding-top:28px;">',
    '<p style="margin:0;border-top:1px solid #303133;padding-top:6px;font-weight:600;">{{doctor_name}}</p>',
    '<p style="margin:4px 0 0;font-size:12px;color:#606266;">Licensed physician / signature</p>',
    '</td>',
    '<td style="border:none;"></td>',
    '</tr>',
    '</table>',
  ].join('');
}

export function horizontalRuleSnippetHtml() {
  return '<hr><p><br></p>';
}
