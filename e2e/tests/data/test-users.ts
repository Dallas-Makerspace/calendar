/** OpenLDAP seed users from dms-ad-openldap/03-users.ldif */
export const testUsers = {
  member: { username: 'user1', password: 'password' },
  memberCommittee: { username: 'user3', password: 'password' },
  admin: { username: 'user2', password: 'password', email: 'user2@dms.local' },
  honorariumAdmin: { username: 'honorariumadmin', password: 'password' },
  financialAdmin: { username: 'financialadmin', password: 'password' },
  disabled: { username: 'disableduser', password: 'password' },
  unknown: { username: 'nonexistent', password: 'password' },
} as const;
