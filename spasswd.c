/* 
 *  C shadowed password file auth code,
 *
 *  contributed by darryl at pointclark dot net.
 *
 * compile: gcc -O2 -s -o spasswd -lcrypt spasswd.c
 * install: install -m 755 spasswd /usr/sbin/spasswd; chmod u+s /usr/sbin/spasswd
*/

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <crypt.h>
#include <shadow.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>

static char salt[12], user[128], pass[128], lockfile[256];

void die(void)
{
  memset(salt, '\0', 12);
  memset(user, '\0', 128);
  memset(pass, '\0', 128);
  memset(lockfile, '\0', 256);
}

int main(int argc, char *argv[])
{
  struct spwd *passwd;
  struct stat finfo;
  FILE *lf;

  atexit(die); die();

  if(fscanf(stdin, "%127s %127s", user, pass) != 2)
    return 1;

  if(!(passwd = getspnam(user)))
    return 1;

  strcpy(lockfile,"/tmp/");
  strncat(lockfile,user,127);
  strcat(lockfile,".spasswd.key");
  if(!stat(lockfile, &finfo)) {
    // a lock file exists, there is another section using spasswd,
    // so we automatically fail this section; this is done because we
    // want to ensure that this program does not provide a hole for
    // crackers on the web...
    sleep(15);
    return 1;
  }
  
  strncpy(salt, passwd->sp_pwdp, 11);
  strncpy(pass, crypt(pass, salt), 127);
  //printf("%s",pass);
  if(!strncmp(pass, passwd->sp_pwdp, 127))
    return 0;

  // auth failed, sleep for a while and create a lock 
  // file to avoid attacks .
  lf=fopen(lockfile,"w+");
  fprintf(lf,"spasswd");
  fclose(lf);
  sync();
  sleep(5);
  remove(lockfile);
  sync();

  return 1;
}
