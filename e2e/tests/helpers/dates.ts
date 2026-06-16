function localIsoDate(date = new Date()): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/** Format for CakePHP datetimepicker fields (mm/dd/yyyy hh:MM tt). */
function formatEventDateTime(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const year = date.getFullYear();
  let hours = date.getHours();
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;
  return `${month}/${day}/${year} ${hours}:${minutes} ${ampm}`;
}

function daysFromNow(days: number, hour = 10, minute = 0): Date {
  const date = new Date();
  date.setDate(date.getDate() + days);
  date.setHours(hour, minute, 0, 0);
  return date;
}

function eventStartEnd(
  daysFromToday: number,
  durationHours = 2,
  hour?: number,
  minute?: number,
): {
  start: string;
  end: string;
  startDate: Date;
  endDate: Date;
} {
  const uniqueHour = hour ?? 8 + (new Date().getSeconds() % 10);
  const uniqueMinute = minute ?? new Date().getMinutes() % 60;
  const startDate = daysFromNow(daysFromToday, uniqueHour, uniqueMinute);
  const endDate = new Date(startDate.getTime() + durationHours * 60 * 60 * 1000);
  return {
    start: formatEventDateTime(startDate),
    end: formatEventDateTime(endDate),
    startDate,
    endDate,
  };
}

export type ScheduleSession = {
  daysFromNow: number;
  startHour: number;
  startMinute?: number;
  durationHours: number;
};

function scheduleSession(session: ScheduleSession) {
  return eventStartEnd(
    session.daysFromNow,
    session.durationHours,
    session.startHour,
    session.startMinute ?? 0,
  );
}

function chicagoYmd(date: Date): string {
  return new Intl.DateTimeFormat('en-CA', {
    timeZone: 'America/Chicago',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(date);
}

/** Matches public event view When column (`E MMM d h:mma` in America/Chicago, `:00` stripped). */
function formatChicagoViewTime(date: Date): string {
  const formatted = new Intl.DateTimeFormat('en-US', {
    timeZone: 'America/Chicago',
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  }).format(date);
  return formatted.replace(/,/g, '').replace(':00', '').replace(/\sAM/g, 'am').replace(/\sPM/g, 'pm');
}

function formatChicagoViewTimeOnly(date: Date): string {
  const formatted = new Intl.DateTimeFormat('en-US', {
    timeZone: 'America/Chicago',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  }).format(date);
  return formatted.replace(':00', '').replace(/\sAM/g, 'am').replace(/\sPM/g, 'pm');
}

/** End time on the event view: time-only when same Chicago calendar day as start. */
function formatChicagoViewEndTime(startDate: Date, endDate: Date): string {
  if (chicagoYmd(startDate) === chicagoYmd(endDate)) {
    return formatChicagoViewTimeOnly(endDate);
  }
  return formatChicagoViewTime(endDate);
}

/** Matches event view cancellation copy (`MMMM d, y — h:mma` in America/Chicago). */
function formatChicagoCancellationDeadline(startDate: Date, cancellationDays: number): string {
  const cutoff = new Date(startDate);
  cutoff.setDate(cutoff.getDate() - cancellationDays);
  const parts = new Intl.DateTimeFormat('en-US', {
    timeZone: 'America/Chicago',
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  }).formatToParts(cutoff);
  const pick = (type: Intl.DateTimeFormatPartTypes) =>
    parts.find((part) => part.type === type)?.value ?? '';
  const hour = pick('hour');
  const minute = pick('minute');
  const dayPeriod = pick('dayPeriod').toLowerCase();
  return `${pick('month')} ${pick('day')}, ${pick('year')} — ${hour}:${minute}${dayPeriod}`;
}

/** Matches owner edit booking display (`MMMM d, y - h:mma` in America/Chicago). */
function formatChicagoBookingTime(date: Date): string {
  const parts = new Intl.DateTimeFormat('en-US', {
    timeZone: 'America/Chicago',
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  }).formatToParts(date);
  const pick = (type: Intl.DateTimeFormatPartTypes) =>
    parts.find((part) => part.type === type)?.value ?? '';
  const hour = pick('hour');
  const minute = pick('minute');
  const dayPeriod = pick('dayPeriod').toLowerCase();
  const minuteSuffix = minute === '00' ? '' : `:${minute}`;
  return `${pick('month')} ${pick('day')}, ${pick('year')} - ${hour}${minuteSuffix}${dayPeriod}`;
}

function addMinutes(date: Date, minutes: number): Date {
  return new Date(date.getTime() + minutes * 60 * 1000);
}

export {
  addMinutes,
  chicagoYmd,
  daysFromNow,
  eventStartEnd,
  formatChicagoBookingTime,
  formatChicagoCancellationDeadline,
  formatChicagoViewEndTime,
  formatChicagoViewTime,
  formatChicagoViewTimeOnly,
  formatEventDateTime,
  localIsoDate,
  scheduleSession,
};
